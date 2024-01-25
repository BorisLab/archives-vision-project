<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Cache(maxage: 0, smaxage: 0, public: false)]
    #[Route(path: ['/', '/connexion'], name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

                // if the user is successful connected, redirect him to his home page with a flash message.
                $this->addFlash('success', 'Vous êtes connecté(e) !');
        
        $response = $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');    
        $response->headers->set('Pragma', 'no-cache');    
        $response->headers->set('Expires', '0');    

        return $response;
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
