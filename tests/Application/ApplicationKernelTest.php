<?php

namespace App\Tests\app;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

final class ApplicationKernelTest extends WebTestCase
{
    public function testUnavailablePageIsShownWhenIndexFileIsMissing(): void
    {
        $client     = self::createClient();
        $projectDir = $client->getContainer()->getParameter('kernel.project_dir');
        $publicDir  = $projectDir . \DIRECTORY_SEPARATOR . 'public';
        $indexFile  = $publicDir . \DIRECTORY_SEPARATOR . 'index.html';
        $backupFile = $publicDir . \DIRECTORY_SEPARATOR . '_index.html';

        $filesystem = new Filesystem();

        if ($filesystem->exists($indexFile)) {
            $filesystem->rename($indexFile, $backupFile);
        }

        try {
            $client->request('GET', '/');
            $response = $client->getResponse();

            self::assertSame(
                Response::HTTP_OK,
                $response->getStatusCode(),
                'Expected unavailable-page handler to respond with HTTP 200.'
            );

            self::assertPageTitleSame(
                'Composer Repository currently not available',
                'Unavailable page title did not match expectations.'
            );
        } finally {
            if ($filesystem->exists($backupFile)) {
                $filesystem->rename($backupFile, $indexFile);
            }
        }
    }
}
