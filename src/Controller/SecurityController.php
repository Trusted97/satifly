<?php

namespace App\Controller;

use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Controller to handle user authentication (login/logout)
 *
 * This controller provides:
 *  - A login form for users
 *  - Access to login errors and last username
 *  - User logout handling
 */
class SecurityController extends AbstractController
{
    /**
     * Display the login form and handle authentication errors.
     *
     * @param AuthenticationUtils $authenticationUtils Provides utilities for login (last username, error)
     */
    #[Route('/login', name: 'login')]
    public function loginAction(AuthenticationUtils $authenticationUtils): Response
    {
        $form = $this->createForm(LoginType::class);
        $username = $authenticationUtils->getLastUsername();
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('views/login.html.twig', [
            'username' => $username,
            'error'    => $error,
            'form'     => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Security $security): Response
    {
        return $security->logout();
    }
}
