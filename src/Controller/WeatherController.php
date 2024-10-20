<?php
// src/Controller/WeatherController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherController extends AbstractController
{
    private $httpClient;
    private $apiKey;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $_ENV['OPENWEATHERMAP_API_KEY'];
    }

    #[Route('/', name: "weather_dashboard", methods: ['GET', 'POST'])]
    public function dashboard(Request $request): Response
    {
        $weatherData = null;
        $error = null;

        if ($request->isMethod('POST')) {
            $city = $request->request->get('city');

            if ($city) {
                try {
                    $response = $this->httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
                        'query' => [
                            'q' => $city,
                            'appid' => $this->apiKey,
                            'units' => 'metric',
                            'lang' => 'fr'
                        ],
                    ]);

                    if ($response->getStatusCode() === 200) {
                        $weatherData = $response->toArray();
                    } else {
                        $error = 'Ville non trouvée ou erreur de l\'API.';
                    }
                } catch (\Exception $e) {
                    $error = 'Erreur lors de la récupération des données météo.';
                }
            } else {
                $error = 'Veuillez entrer un nom de ville.';
            }
        }

        return $this->render('weather/dashboard.html.twig', [
            'weatherData' => $weatherData,
            'error' => $error,
        ]);
    }
}