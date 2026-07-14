<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller for serving the main index page.
 *
 * This controller checks whether a public `index.html` file exists in the
 * project’s `public` directory. If it does, the file content is served directly.
 * If not, a "site unavailable" page is rendered.
 */
class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function indexAction(ParameterBagInterface $parameterBag): Response
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $isAuthEnabled = $parameterBag->get('admin.auth');
        $indexFile = $projectDir . '/public/index.html';

        if (!\is_file($indexFile)) {
            return $this->render('views/unavailable.html.twig', [
                'isAuthEnabled' => $isAuthEnabled,
            ]);
        }

        return new Response(
            \file_get_contents($indexFile),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }
}
