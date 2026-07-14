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

/**
 * Controller for managing Satis operations.
 *
 * Provides functionality for:
 *  - Viewing the Satis build page
 *  - Running Satis builds
 *  - Editing the Satis configuration
 *
 * Access to these actions is restricted to admin users.
 */
class SatisController extends AbstractProtectedController
{
    /**
     * Constructor.
     *
     * @param ParameterBagInterface $parameterBag Provides access to application parameters
     */
    public function __construct(public ParameterBagInterface $parameterBag)
    {
    }

    /**
     * Display the Satis build page.
     *
     * Provides a UI page for triggering Satis builds.
     */
    #[Route('/admin/satis/build', name: 'satis_build', methods: ['GET'])]
    public function buildAction(): Response
    {
        $this->checkAccess(); // Ensure user is admin
        $isAuthEnabled = $this->parameterBag->get('admin.auth');

        return $this->render('views/satis-build.html.twig', [
            'isAuthEnabled' => $isAuthEnabled,
        ]);
    }

    /**
     * Execute a Satis build.
     *
     * Runs the SatisManager service to build the repository index
     * and returns the output wrapped in a ProcessResponse.
     *
     * @param SatisManager $satisManager Service for running Satis builds
     */
    #[Route('/admin/satis/buildRun', name: 'satis_build_run', methods: ['GET'])]
    public function buildRunAction(SatisManager $satisManager): Response
    {
        $this->checkAccess(); // Ensure user is admin
        $output = $satisManager->run(); // Run the Satis build process

        return ProcessResponse::createFromOutput($output); // Return formatted process output
    }

    /**
     * Edit the Satis configuration.
     *
     * Displays a form to edit the configuration and saves changes.
     *
     * @param Request           $request The current HTTP request
     * @param RepositoryManager $manager Service managing repository configurations
     *
     * @throws \JsonException When JSON encoding/decoding fails
     */
    #[Route('/admin/satis/config', name: 'satis_config', methods: ['GET', 'POST'])]
    public function editSatisConfigAction(Request $request, RepositoryManager $manager): Response
    {
        $this->checkAccess(); // Ensure user is admin
        $isAuthEnabled = $this->parameterBag->get('admin.auth');

        // Get current configuration
        $config = $manager->getConfig();

        // Create and handle the configuration form
        $form = $this->createForm(SatisConfigType::class, $config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush(); // Persist configuration changes
            $this->addFlash('success', 'Configuration saved!');
        }

        return $this->render('views/satis-config.html.twig', [
            'form'          => $form->createView(),
            'isAuthEnabled' => $isAuthEnabled,
        ]);
    }
}
