<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function indexAction(ParameterBagInterface $parameterBag): Response
    {
        $projectDir    = $this->getParameter('kernel.project_dir');
        $isAuthEnabled = $parameterBag->get('admin.auth');

        $indexFile  = $projectDir . '/public/index.html';

        if (!\file_exists($indexFile)) {
            return $this->render('views/unavailable.html.twig',
                [
                    'isAuthEnabled' => $isAuthEnabled,
                ]);
        }

        return new Response(\file_get_contents($indexFile));
    }
}
