<?php

namespace App\Tests\Controller;

use App\Tests\Traits\VfsTrait;
use org\bovigo\vfs\vfsStreamFile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminControllerTest extends WebTestCase
{
    use VfsTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vfsSetup();
    }

    protected function tearDown(): void
    {
        $this->vfsTearDown();
        parent::tearDown();
    }

    /**
     * @throws \JsonException
     */
    public function testRepositoryIndex(): void
    {
        $client   = self::createClient();
        $crawler  = $client->request('GET', '/admin');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $buttons = $crawler->filterXPath('//a');
        $this->assertNotEmpty($buttons);
        $this->assertGreaterThan(3, $buttons->count());
    }

    public function testRepositoryCRUD(): void
    {
        $client = self::createClient();
        $client->disableReboot();
        $crawler  = $client->request('GET', '/admin/new');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $form = $crawler->filterXPath('//form[@id="new_repository"]');
        $this->assertSame(Request::METHOD_POST, \mb_strtoupper($form->attr('method')));

        // form validation must fail due to invalid url
        $client->submit($form->form());
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $url = 'git@github.com:YourAccount/YourRepo.git';
        $client->submit(
            $form->form(),
            [
                'RepositoryType[type]'               => 'git',
                'RepositoryType[name]'               => 'lovelyTest',
                'RepositoryType[url]'                => $url,
                'RepositoryType[installationSource]' => 'dist',
            ]
        );
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $this->assertTrue($this->vfsRoot->hasChild('satis.json'));
        /** @var vfsStreamFile $configHandle */
        $configHandle = $this->vfsRoot->getChild('satis.json');
        $config       = $configHandle->getContent();

        $this->assertJson($config);
        $config = \json_decode($config, false);
        $this->assertNotEmpty($config);

        self::assertObjectHasProperty('repositories', $config);

        $repositories = \get_object_vars($config->repositories);
        $this->assertNotEmpty($repositories);
        $firstRepo = \reset($repositories);

        $this->assertSame($url, $firstRepo->url);
        $this->assertSame('git', $firstRepo->type);
        $this->assertSame('dist', $firstRepo->{'installation-source'});

        $url2    = 'git@github.com:account/repository.git';
        $crawler = $client->request('GET', '/admin/edit/' . \md5($url));
        $form    = $crawler->filterXPath('//form[@id="edit_repository"]');

        $client->submit(
            $form->form(),
            [
                'RepositoryType[type]'               => 'github',
                'RepositoryType[url]'                => $url2,
                'RepositoryType[name]'               => 'lovelyTest2',
                'RepositoryType[installationSource]' => 'source',
            ]
        );

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        /** @var vfsStreamFile $configHandle */
        $configHandle = $this->vfsRoot->getChild('satis.json');
        $config       = \json_decode($configHandle->getContent(), false, 512, \JSON_THROW_ON_ERROR);

        $repositories = \get_object_vars($config->repositories);
        $this->assertNotEmpty($repositories);
        $firstRepo = \reset($repositories);

        $this->assertSame($url2, $firstRepo->url);
        $this->assertSame('github', $firstRepo->type);
        $this->assertSame('source', $firstRepo->{'installation-source'});

        $client->request('DELETE', '/admin/delete/' . \md5($url2));
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
    }
}
