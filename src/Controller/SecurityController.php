<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;



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

                if($error){
                    $this->addFlash('error', 'Email ou mot de passe incorrect !');
                }
        
        $loginResponse = $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);        
        $loginResponse->setCache([
            'must_revalidate'  => true,
            'no_cache'         => true,
            'no_store'         => true,
            'no_transform'     => false,
            'public'           => false,
            'private'          => true,
            'max_age'          => 0,
        ]); 

        return $loginResponse;
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
