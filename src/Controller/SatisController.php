<?php

namespace App\Controller;

use App\Form\SatisConfigType;
use App\Process\ProcessResponse;
use App\Service\RepositoryManager;
use App\Service\SatisManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SatisController extends AbstractProtectedController
{
    public function __construct(public ParameterBagInterface $parameterBag)
    {
    }

    #[Route('/admin/satis/build', name: 'satis_build', methods: ['GET'])]
    public function buildAction(): Response
    {
        $this->checkAccess();
        $isAuthEnabled = $this->parameterBag->get('admin.auth');

        return $this->render('views/satis-build.html.twig', [
            'isAuthEnabled' => $isAuthEnabled,
        ]);
    }

    #[Route('/admin/satis/buildRun', name: 'satis_build_run', methods: ['GET'])]
    public function buildRunAction(SatisManager $satisManager): Response
    {
        $this->checkAccess();
        $output = $satisManager->run();

        return ProcessResponse::createFromOutput($output);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/admin/satis/config', name: 'satis_config', methods: ['GET', 'POST'])]
    public function editSatisConfigAction(Request $request, RepositoryManager $manager): Response
    {
        $this->checkAccess();

        $isAuthEnabled = $this->parameterBag->get('admin.auth');
        $config        = $manager->getConfig();

        $form = $this->createForm(SatisConfigType::class, $config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash('success', 'Configuration saved!');
        }

        return $this->render('views/satis-config.html.twig', [
            'form'          => $form->createView(),
            'isAuthEnabled' => $isAuthEnabled,
        ]);
    }
}
