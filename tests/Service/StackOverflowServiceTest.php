<?php

namespace App\Tests\Service;

use App\Service\StackOverflowService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class StackOverflowServiceTest extends TestCase
{
    private $httpClient;
    private $service;
    private $apiUrl = 'https://api.stackoverflow.com/2.3/questions'; // URL simulada para pruebas

    protected function setUp(): void
    {
        // Crear un mock de HttpClientInterface
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        // Instanciar el servicio con el mock del cliente HTTP
        $this->service = new StackOverflowService($this->httpClient, $this->apiUrl);
    }

    public function testFetchQuestionsSuccess()
    {
        $response = $this->createMock(ResponseInterface::class);

        // Definir datos simulados de respuesta de la API
        $responseData = [
            'items' => [
                ['question_id' => 1, 'title' => 'Example Question 1', 'tags' => ['php'], 'link' => 'http://example.com/q1'],
                ['question_id' => 2, 'title' => 'Example Question 2', 'tags' => ['symfony'], 'link' => 'http://example.com/q2'],
            ]
        ];

        // Configurar el mock para devolver un código de estado 200 y los datos de respuesta
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($responseData);

        // Configurar el cliente HTTP para devolver el mock de respuesta
        $this->httpClient->method('request')->willReturn($response);

        // Ejecutar el método a probar
        $questions = $this->service->fetchQuestions('php');

        // Verificar que los datos devueltos son correctos
        $this->assertCount(2, $questions);
        $this->assertEquals(1, $questions[0]['question_id']);
        $this->assertEquals('Example Question 1', $questions[0]['title']);
    }

    public function testFetchQuestionsClientException()
    {
        // Configurar el cliente HTTP para lanzar una excepción de cliente
        $this->httpClient->method('request')
            ->willThrowException($this->createMock(ClientExceptionInterface::class));

        // Verificar que se lanza una excepción con un mensaje adecuado
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to fetch data from Stack Overflow API');

        $this->service->fetchQuestions('php');
    }

    public function testFetchQuestionsServerException()
    {
        // Configurar el cliente HTTP para lanzar una excepción de servidor
        $this->httpClient->method('request')
            ->willThrowException($this->createMock(ServerExceptionInterface::class));

        // Verificar que se lanza una excepción con un mensaje adecuado
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to fetch data from Stack Overflow API');

        $this->service->fetchQuestions('php');
    }

    public function testFetchQuestionsTransportException()
    {
        // Configurar el cliente HTTP para lanzar una excepción de transporte
        $this->httpClient->method('request')
            ->willThrowException($this->createMock(TransportExceptionInterface::class));

        // Verificar que se lanza una excepción con un mensaje adecuado
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to fetch data from Stack Overflow API');

        $this->service->fetchQuestions('php');
    }
}
