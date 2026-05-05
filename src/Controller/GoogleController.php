<?php

namespace App\Controller;

use League\OAuth2\Client\Provider\Google;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connect(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_dashboard');
        }
        
        $provider = new Google([
            'clientId'     => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
            'redirectUri'  => $request->getSchemeAndHttpHost() . '/connect/google/check',
        ]);
        
        $authUrl = $provider->getAuthorizationUrl();
        $request->getSession()->set('oauth2state', $provider->getState());
        
        return $this->redirect($authUrl);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function check(): Response
    {
        // This route is handled by GoogleAuthenticator
        // If we reach here directly, something went wrong
        return $this->redirectToRoute('login');
    }
}