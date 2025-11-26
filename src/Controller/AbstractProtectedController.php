<?php

namespace App\Controller;

use App\Service\RepositoryManager;
use App\Validator\EnvironmentValidatorInterface;
use App\Validator\EnvValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractProtectedController extends AbstractController
{
    /**
     * Check admin access.
     */
    protected function checkAccess(): void
    {
        if (!$this->getParameter('admin.auth')) {
            return;
        }

        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    }

    protected function checkEnvironment(EnvironmentValidatorInterface $validator): void
    {
        try {
            $validator->validate();
        } catch (\RuntimeException $exception) {
            $this->addFlash('warning', $exception->getMessage());
        }
    }

    public static function getSubscribedServices(): array
    {
        $services   = parent::getSubscribedServices();
        $services[] = EnvValidator::class;
        $services[] = RepositoryManager::class;

        return $services;
    }
}
