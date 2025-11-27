<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityControllerTest extends WebTestCase
{
    public function testAdminLoginFlow(): void
    {
        $client = self::createClient(['environment' => 'testsecure']);

        // --- REDIRECT TO LOGIN ---
        $client->request(Request::METHOD_GET, '/admin');
        $response = $client->getResponse();

        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode(), 'Accessing /admin without login must redirect.');
        self::assertStringEndsWith('/login', $response->headers->get('location'), 'Redirect must go to /login.');

        // --- LOGIN FORM DISPLAY ---
        $crawler  = $client->request(Request::METHOD_GET, '/login');
        $response = $client->getResponse();
        self::assertTrue($response->isSuccessful(), 'Login page must respond with 200.');

        $form = $crawler->filterXPath('//form[@id="login_form"]')->form();

        // --- SUBMIT EMPTY PASSWORD ---
        $client->submit($form, [
            'login[username]' => 'test',
            'login[password]' => '',
        ]);

        $response = $client->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response, 'Invalid login submission must redirect.');
        self::assertTrue($response->isRedirection(), 'Response must be a redirection.');
        self::assertStringEndsWith('/login', $response->getTargetUrl(), 'Failed login must redirect back to /login.');

        // --- SUBMIT CORRECT CREDENTIALS ---
        $client->submit($form, [
            'login[username]' => 'test',
            'login[password]' => 'test',
        ]);

        $response = $client->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response, 'Successful login must redirect.');
        self::assertTrue($response->isRedirection(), 'Response must be a redirection.');
        self::assertStringEndsWith('/admin', $response->getTargetUrl(), 'Successful login must redirect to /admin.');

        // --- ACCESS ADMIN AFTER LOGIN ---
        $client->request(Request::METHOD_GET, '/admin');
        $response = $client->getResponse();
        self::assertTrue($response->isSuccessful(), 'Authenticated user must be able to access /admin.');
    }
}
