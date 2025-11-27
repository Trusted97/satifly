<?php

namespace App\Tests\Controller;

use App\Tests\Traits\VfsTrait;
use org\bovigo\vfs\vfsStreamFile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AdminControllerTest extends WebTestCase
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

    public function testRepositoryIndexPageDisplaysLinks(): void
    {
        $client   = self::createClient();
        $crawler  = $client->request('GET', '/admin');
        $response = $client->getResponse();

        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Admin index page must respond with 200.');

        $links = $crawler->filterXPath('//a');
        self::assertNotEmpty($links, 'Admin page must contain links.');
        self::assertGreaterThan(3, $links->count(), 'Expected at least 4 links on the admin index page.');
    }

    public function testRepositoryCRUDWorkflow(): void
    {
        $client = self::createClient();
        $client->disableReboot();

        // --- CREATE NEW REPOSITORY ---
        $crawler  = $client->request('GET', '/admin/new');
        $response = $client->getResponse();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), 'New repository page must respond with 200.');

        $form = $crawler->filterXPath('//form[@id="new_repository"]')->form();
        self::assertSame(Request::METHOD_POST, \mb_strtoupper($form->getMethod()), 'Form must use POST method.');

        // Submit invalid form (should fail validation)
        $client->submit($form);
        self::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode(), 'Invalid form submission should reload page.');

        // Submit valid form
        $firstUrl = 'git@github.com:YourAccount/YourRepo.git';
        $client->submit($form, [
            'RepositoryType[type]'               => 'git',
            'RepositoryType[name]'               => 'lovelyTest',
            'RepositoryType[url]'                => $firstUrl,
            'RepositoryType[installationSource]' => 'dist',
        ]);

        $response = $client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode(), 'Form submission must redirect.');

        $this->assertRepositoryInConfig($firstUrl, 'git', 'dist');

        // --- EDIT REPOSITORY ---
        $secondUrl = 'git@github.com:account/repository.git';
        $crawler   = $client->request('GET', '/admin/edit/' . \md5($firstUrl));
        $editForm  = $crawler->filterXPath('//form[@id="edit_repository"]')->form();

        $client->submit($editForm, [
            'RepositoryType[type]'               => 'github',
            'RepositoryType[url]'                => $secondUrl,
            'RepositoryType[name]'               => 'lovelyTest2',
            'RepositoryType[installationSource]' => 'source',
        ]);

        $response = $client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode(), 'Edit submission must redirect.');

        $this->assertRepositoryInConfig($secondUrl, 'github', 'source');

        // --- DELETE REPOSITORY ---
        $client->request('DELETE', '/admin/delete/' . \md5($secondUrl));
        $response = $client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode(), 'Delete request must redirect.');
    }

    /**
     * Helper method to assert repository exists in vfs satis.json
     */
    private function assertRepositoryInConfig(string $url, string $type, string $installationSource): void
    {
        self::assertTrue($this->vfsRoot->hasChild('satis.json'), 'satis.json must exist in vfsRoot.');

        /** @var vfsStreamFile $configFile */
        $configFile = $this->vfsRoot->getChild('satis.json');
        $config     = \json_decode($configFile->getContent(), false, 512, \JSON_THROW_ON_ERROR);

        self::assertObjectHasProperty('repositories', $config, 'satis.json must contain "repositories".');

        $repositories = \get_object_vars($config->repositories);
        self::assertNotEmpty($repositories, 'There must be at least one repository in the config.');

        $firstRepo = \reset($repositories);

        self::assertSame($url, $firstRepo->url, 'Repository URL must match.');
        self::assertSame($type, $firstRepo->type, 'Repository type must match.');
        self::assertSame($installationSource, $firstRepo->{'installation-source'}, 'Installation source must match.');
    }
}
