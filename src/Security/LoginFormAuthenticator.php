<?php

namespace App\Security;

use App\Entity\StatutUtilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';
    private $security;
    private UtilisateurRepository $utilisateurRepository;

    public function __construct(private UrlGeneratorInterface $urlGenerator, UtilisateurRepository $utilisateurRepository,Security $security)
    {
        $this->security = $security;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email, function ($userIdentifier) {
                $user = $this->utilisateurRepository->findOneBy(['email' => $userIdentifier]);
    
                if (!$user) {
                    throw new CustomUserMessageAuthenticationException('Identifiants invalides.');
                }
    
                // Vérifier si l'utilisateur a un statut "Inactif"
                if ($user->getStatut() === StatutUtilisateur::INACTIF) {
                    throw new CustomUserMessageAuthenticationException('Connexion impossible.');
                }
    
                return $user;
            }),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        
        $user = $token->getUser();

        $roles = $user->getRoles();

        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);

        if ($targetPath){
            if (in_array('ROLE_ADMIN', $roles)) {
                return new RedirectResponse($this->urlGenerator->generate('app_admin_home'));
            } elseif (in_array('ROLE_ARCHIVIST', $roles)) {
                return new RedirectResponse($this->urlGenerator->generate('app_archivist_home'));
            } elseif (in_array('ROLE_USER', $roles)) {
                return new RedirectResponse($this->urlGenerator->generate('app_user_home'));
            } else {
                return new RedirectResponse($this->urlGenerator->generate('app_login'));
            }
        }

        else {
            if(in_array('ROLE_ADMIN', $roles)){
                return new RedirectResponse($this->urlGenerator->generate('app_admin_home'));
            // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
            } elseif(in_array('ROLE_ARCHIVIST', $roles)){
                 // if the user is successful connected, redirect him to his home page with a flash message.
                return new RedirectResponse($this->urlGenerator->generate('app_archivist_home'));
            } elseif(in_array('ROLE_USER', $roles)){
                return new RedirectResponse($this->urlGenerator->generate('app_user_home'));
                // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
            } else{
                return RedirectResponse($this->urlGenerator->generate('app_login'));
            }
        }
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    private function setUserMercureCookie(Request $request, Authorization $authorization)
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        $baseUrl = $this->params->get('app.base_url');
        $authorization->setCookie($request, [
            "{$baseUrl}/users/{$user->getId()}" // Définit le topic auquel cet utilisateur peut s'abonner
        ]);
    }

    private function setArchivistMercureCookie(Request $request, Authorization $authorization)
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        $baseUrl = $this->params->get('app.base_url');
        $authorization->setCookie($request, [
            "{$baseUrl}/archivists" // Définit le topic auquel cet utilisateur peut s'abonner
        ]);
    }
}
