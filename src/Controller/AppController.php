<?php

namespace App\Controller;

use App\Form\CoverType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class AppController extends AbstractController
{
    private HttpClientInterface $httpClient;
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/new-cover-letter', name: 'app_app')]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->isPaiement()) {
            $form = $this->createForm(CoverType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                $apiKey = $_ENV['OPENAI_API_KEY'];

                try {
                    $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $apiKey,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'model' => 'gpt-4.1',
                            'messages' => [
                                [
                                    'role' => 'user',
                                    'content' => "Génère une lettre de motivation pour {$data['nom']} {$data['prenom']}, " .
                                        "diplômé en {$data['diplome']}, qui postule au poste de {$data['poste']} " .
                                        "chez {$data['entreprise']}. Voici l'annonce: {$data['annonce']}."
                                ]
                            ],
                            'temperature' => 0.7,
                            'max_tokens' => 1500,
                        ],
                        'timeout' => 30,
                        'buffer' => true,
                    ]);

                    $result = $response->toArray();
                    $message = $result['choices'][0]['message']['content'];
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue: ' . $e->getMessage());
                    $message = null;
                }
            }

            return $this->render('app/index.html.twig', [
                'controller_name' => 'AppController',
                'form' => $form,
                'message' => $message ?? null,
            ]);
        }

        return $this->redirectToRoute('app_home');
    }
}
