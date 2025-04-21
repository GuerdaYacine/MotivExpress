<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/redirect', name: 'app_redirect')]
    public function redirectUser(UserRepository $userRepository, Security $security): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user = $userRepository->findOneBy(['email' => $security->getUser()->getUserIdentifier()]);

        if (!$user->isPaiement()) {
            return $this->redirectToRoute('app_stripe');
        }

        return $this->redirectToRoute('app_app');
    }
}
