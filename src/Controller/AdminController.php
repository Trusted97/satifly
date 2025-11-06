<?php

namespace App\Controller;

use App\DTO\Repository;
use App\Event\BuildEvent;
use App\Form\ComposerLockType;
use App\Form\DeleteFormType;
use App\Form\RepositoryType;
use App\Service\LockProcessor;
use App\Service\RepositoryManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdminController extends AbstractProtectedController
{
    public function __construct(
        private readonly RepositoryManager $repositoryManager,
        private readonly LockProcessor $lockProcessor,
        public readonly ParameterBagInterface $parameterBag,
    ) {
    }

    #[Route('/admin', name: 'admin', methods: ['GET'])]
    public function indexAction(): Response
    {
        $this->checkAccess();
        $this->checkEnvironment();

        $repositories  = $this->repositoryManager->getRepositories();
        $isAuthEnabled = $this->parameterBag->get('admin.auth');

        return $this->render('views/home.html.twig', [
            'repositories'  => $repositories,
            'isAuthEnabled' => $isAuthEnabled,
        ]);
    }

    #[Route('/admin/new', name: 'repository_new', methods: ['GET', 'POST'])]
    public function newAction(Request $request): Response
    {
        $this->checkAccess();
        $isAuthEnabled = $this->parameterBag->get('admin.auth');

        $repository = new Repository();
        $form       = $this->createForm(
            RepositoryType::class,
            $repository
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->repositoryManager->add($form->getData());
                $this->addFlash('success', 'New repository added successfully');

                return $this->redirectToRoute('admin');
            } catch (\Exception $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('views/new.html.twig', [
            'form'          => $form->createView(),
            'isAuthEnabled' => $isAuthEnabled,
        ]);
    }

    #[Route('/admin/upload', name: 'repository_upload', methods: ['GET', 'POST'])]
    public function uploadAction(Request $request): Response
    {
        $this->checkAccess();
        $isAuthEnabled = $this->parameterBag->get('admin.auth');

        $form = $this->createForm(ComposerLockType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $lock = $form->get('file')->getData()->openFile();
                $this->lockProcessor->processFile($lock);
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

        $form = $this->createForm(RepositoryType::class, clone $repository, [
            'show_full_update' => true,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $fullUpdate = $form->get('fullUpdate')->getData();

                // Update satis.json config
                $updatedRepository = $this->repositoryManager->update($repository, $form->getData());

                if ($fullUpdate) {
                    // Build && Update single repository
                    $buildEvent = new BuildEvent($updatedRepository);
                    $dispatcher->dispatch($buildEvent, BuildEvent::class);
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

    #[Route('/admin/delete/{repository}', name: 'repository_delete', requirements: ['repository' => '[a-zA-Z0-9_-]+'], methods: ['GET', 'DELETE'])]
    public function deleteAction(Request $request): Response
    {
        $this->checkAccess();
        $isAuthEnabled = $this->parameterBag->get('admin.auth');
        $repository    = $this->repositoryManager->findOneRepository($request->attributes->get('repository'));
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

        return $this->render(
            'views/delete.html.twig', [
                'form'          => $form->createView(),
                'repository'    => $repository,
                'isAuthEnabled' => $isAuthEnabled,
            ]
        );
    }
}
