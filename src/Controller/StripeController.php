<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class StripeController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/paiement', name: 'app_stripe')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($user->isPaiement()) {
            $this->addFlash('info', 'Vous avez déjà accès à nos services premium.');
            return $this->redirectToRoute('app_app');
        }

        return $this->render('stripe/index.html.twig');
    }

    #[Route('/create-checkout-session', name: 'app_stripe_create_checkout_session')]
    public function createCheckoutSession(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($user->isPaiement()) {
            $this->addFlash('info', 'Vous avez déjà accès à nos services premium.');
            return $this->redirectToRoute('app_app');
        }

        try {
            $apiKey = $_ENV['STRIPE_API_KEY'];

            $response = $this->httpClient->request('POST', 'https://api.stripe.com/v1/checkout/sessions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'customer_email' => $user->getUserIdentifier(),
                    'line_items[0][price_data][currency]' => 'usd',
                    'line_items[0][price_data][product_data][name]' => 'MotivExpress',
                    'line_items[0][price_data][unit_amount]' => 1000,
                    'line_items[0][quantity]' => 1,
                    'mode' => 'payment',
                    'success_url' => 'http://127.0.0.1:8000/success',
                    'cancel_url' => 'http://127.0.0.1:8000/cancel',
                ],
                'timeout' => 15,
                'buffer' => true,
            ]);

            $result = $response->toArray();

            return $this->redirect($result['url']);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la création de la session de paiement: ' . $e->getMessage());
            return $this->redirectToRoute('app_stripe');
        }
    }

    #[Route('/success', name: 'app_stripe_success')]
    public function success(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $user->setPaiement(true);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Paiement réussi ! Vous avez maintenant accès à toutes nos fonctionnalités.');
        return $this->redirectToRoute('app_app');
    }

    #[Route('/cancel', name: 'app_stripe_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Le paiement a été annulé. Vous pouvez réessayer quand vous le souhaitez.');
        return $this->redirectToRoute('app_stripe');
    }
}
