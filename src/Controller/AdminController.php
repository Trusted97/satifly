<?php

namespace App\Controller;

use App\DTO\Repository;
use App\Event\BuildEvent;
use App\Form\ComposerLockType;
use App\Form\DeleteFormType;
use App\Form\RepositoryType;
use App\Service\LockProcessor;
use App\Service\RepositoryManager;
use App\Validator\EnvValidator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Controller for administrative tasks.
 *
 * Provides functionality to:
 *  - View all repositories
 *  - Create a new repository
 *  - Upload and parse composer.lock files
 *  - Edit existing repositories
 *  - Delete repositories
 *
 * Access to these actions is restricted to admin users.
 */
class AdminController extends AbstractProtectedController
{
    /**
     * Constructor.
     *
     * @param RepositoryManager     $repositoryManager service for repository CRUD operations
     * @param LockProcessor         $lockProcessor     Service for processing composer.lock files.
     * @param ParameterBagInterface $parameterBag      provides access to application parameters
     */
    public function __construct(
        private readonly RepositoryManager $repositoryManager,
        private readonly LockProcessor $lockProcessor,
        public readonly ParameterBagInterface $parameterBag,
    ) {
    }

    /**
     * Admin dashboard.
     *
     * Displays all repositories and shows whether admin authentication is enabled.
     *
     * @param EnvValidator $validator Environment validator
     */
    #[Route('/admin', name: 'admin', methods: ['GET'])]
    public function indexAction(EnvValidator $validator): Response
    {
        $this->checkAccess();       // Ensure current user is admin
        $this->checkEnvironment($validator); // Check environment and add flash warning if invalid

        $repositories  = $this->repositoryManager->getRepositories();
        $isAuthEnabled = $this->parameterBag->get('admin.auth');

        return $this->render('views/home.html.twig', [
            'repositories'  => $repositories,
            'isAuthEnabled' => $isAuthEnabled,
        ]);
    }

    /**
     * Create a new repository.
     *
     * Handles form submission to add a new repository.
     */
    #[Route('/admin/new', name: 'repository_new', methods: ['GET', 'POST'])]
    public function newAction(Request $request): Response
    {
        $this->checkAccess();
        $isAuthEnabled = $this->parameterBag->get('admin.auth');

        $repository = new Repository();
        $form       = $this->createForm(RepositoryType::class, $repository);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->repositoryManager->add($form->getData());
                $this->addFlash('success', 'New repository added successfully');

                return $this->redirectToRoute('admin');
            } catch (\Exception $e) {
                $form->addError(new FormError($e->getMessage())); // Show error in the form
            }
        }

        return $this->render('views/new.html.twig', [
            'form'          => $form->createView(),
            'isAuthEnabled' => $isAuthEnabled,
        ]);
    }

    /**
     * Upload composer.lock file.
     *
     * Processes a composer.lock file and stores its information.
     */
    #[Route('/admin/upload', name: 'repository_upload', methods: ['GET', 'POST'])]
    public function uploadAction(Request $request): Response
    {
        $this->checkAccess();
        $isAuthEnabled = $this->parameterBag->get('admin.auth');

        $form = $this->createForm(ComposerLockType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $lock = $form->get('file')->getData()->openFile(); // Open uploaded file
                $this->lockProcessor->processFile($lock);          // Parse and process composer.lock
                $this->addFlash('success', 'Composer lock file parsed successfully');

                return $this->redirectToRoute('admin');
            } catch (\Exception $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('views/upload.html.twig', [
            'form'          => $form->createView(),
            'isAuthEnabled' => $isAuthEnabled,
        ]);
    }

    /**
     * Edit an existing repository.
     *
     * Handles repository updates and optionally triggers a build event for full updates.
     */
    #[Route('/admin/edit/{repository}', name: 'repository_edit', requirements: ['repository' => '[a-zA-Z0-9_-]+'], methods: ['GET', 'POST'])]
    public function editAction(Request $request, EventDispatcherInterface $dispatcher): Response
    {
        $this->checkAccess();
        $isAuthEnabled = $this->parameterBag->get('admin.auth');

        $repository = $this->repositoryManager->findOneRepository($request->attributes->get('repository'));

        if (!$repository) {
            $this->addFlash('error', \sprintf('No repository found with this id %s', $request->attributes->get('repository')));

            return $this->redirectToRoute('admin');
        }

        $form = $this->createForm(RepositoryType::class, clone $repository, ['show_full_update' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $fullUpdate        = $form->get('fullUpdate')->getData();
                $updatedRepository = $this->repositoryManager->update($repository, $form->getData());

                if ($fullUpdate) {
                    $dispatcher->dispatch(new BuildEvent($updatedRepository), BuildEvent::class);
                }

                $this->addFlash('success', 'Repository updated successfully');

                return $this->redirectToRoute('admin');
            } catch (\Exception $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('views/edit.html.twig', [
            'form'          => $form->createView(),
            'isAuthEnabled' => $isAuthEnabled,
        ]);
    }

    /**
     * Delete a repository.
     *
     * Handles the DELETE form submission and removes the repository.
     */
    #[Route('/admin/delete/{repository}', name: 'repository_delete', requirements: ['repository' => '[a-zA-Z0-9_-]+'], methods: ['GET', 'DELETE'])]
    public function deleteAction(Request $request): Response
    {
        $this->checkAccess();
        $isAuthEnabled = $this->parameterBag->get('admin.auth');

        $repository = $this->repositoryManager->findOneRepository($request->attributes->get('repository'));
        if (!$repository) {
            return $this->redirectToRoute('admin');
        }

        $form = $this->createForm(DeleteFormType::class, null, [
            'method' => 'DELETE',
            'entity' => $repository,
        ]);

        if (Request::METHOD_DELETE === $request->getMethod()) {
            try {
                $this->repositoryManager->delete($repository);
                $this->addFlash('success', 'Repository removed successfully');

                return $this->redirectToRoute('admin');
            } catch (\Exception $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('views/delete.html.twig', [
            'form'          => $form->createView(),
            'repository'    => $repository,
            'isAuthEnabled' => $isAuthEnabled,
        ]);
    }
}
