<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class StackOverflowService
{
    private HttpClientInterface $httpClient;
    private string $apiUrl;

    public function __construct(HttpClientInterface $httpClient, string $apiUrl)
    {
        $this->httpClient = $httpClient;
        $this->apiUrl = $apiUrl;
    }

    public function fetchQuestions(string $tagged, ?\DateTime $fromDate = null, ?\DateTime $toDate = null): array
    {
        $url = $this->apiUrl . '?order=desc&sort=activity&tagged=' . $tagged . '&site=stackoverflow';

        if ($fromDate) {
            $url .= '&fromdate=' . $fromDate->getTimestamp();
        }
        if ($toDate) {
            $url .= '&todate=' . $toDate->getTimestamp();
        }

        try {
            $response = $this->httpClient->request('GET', $url);

            // Comprobar el estado de la respuesta
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Error fetching data from Stack Overflow API: ' . $response->getContent(false));
            }

            return $response->toArray()['items'];
        } catch (TransportExceptionInterface | ClientExceptionInterface | ServerExceptionInterface $e) {
            // Lanzar una excepción con un mensaje genérico que pueda ser manejado por el controlador
            throw new \Exception('Failed to fetch data from Stack Overflow API: ' . $e->getMessage());
        }
    }
}

