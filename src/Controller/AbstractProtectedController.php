<?php

namespace App\Controller;

use App\Service\RepositoryManager;
use App\Validator\EnvironmentValidatorInterface;
use App\Validator\EnvValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Abstract base controller for protected areas of the application.
 *
 * Provides common functionality such as:
 *  - Checking if the current user has admin access
 *  - Validating the application environment
 *  - Declaring additional subscribed services
 *
 * This controller is meant to be extended by other controllers
 * that require these common protections.
 */
abstract class AbstractProtectedController extends AbstractController
{
    /**
     * Checks whether the current user has admin privileges.
     *
     * If the 'admin.auth' parameter is disabled, the method does nothing.
     * Otherwise, it enforces ROLE_ADMIN access and will throw an exception
     * if the current user is not granted admin rights.
     */
    protected function checkAccess(): void
    {
        // Skip access check if admin authentication is disabled
        if (!$this->getParameter('admin.auth')) {
            return;
        }

        // Deny access unless the user has ROLE_ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    }

    /**
     * Validates the application environment using the given validator.
     *
     * If the validator throws a RuntimeException, a flash message is added
     * to inform the user, but execution continues.
     */
    protected function checkEnvironment(EnvironmentValidatorInterface $validator): void
    {
        try {
            $validator->validate();
        } catch (\RuntimeException $exception) {
            // Display warning message to the user
            $this->addFlash('warning', $exception->getMessage());
        }
    }

    /**
     * Returns the list of services that this controller depends on.
     *
     * By default, includes the services declared in the parent class,
     * and additionally subscribes EnvValidator and RepositoryManager.
     *
     * This allows Symfony's service container to inject these services
     * when needed.
     *
     * @return array<string>
     */
    public static function getSubscribedServices(): array
    {
        $services   = parent::getSubscribedServices();
        $services[] = EnvValidator::class;
        $services[] = RepositoryManager::class;

        return $services;
    }
}
