<?php

namespace App\Tests\Controller;

use App\Controller\StackOverflowController;
use App\Entity\Question;
use App\Service\ApiErrorHandler;
use App\Service\QuestionService;
use App\Service\StackOverflowService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class StackOverflowControllerTest extends TestCase
{
    private $stackOverflowService;
    private $questionService;
    private $apiErrorHandler;
    private $controller;

    protected function setUp(): void
    {
        $this->stackOverflowService = $this->createMock(StackOverflowService::class);
        $this->questionService = $this->createMock(QuestionService::class);
        $this->apiErrorHandler = $this->createMock(ApiErrorHandler::class);

        $this->controller = new StackOverflowController(
            $this->stackOverflowService,
            $this->questionService,
            $this->apiErrorHandler
        );
    }

    public function testGetQuestionsFromDatabase()
    {
        // Crear una instancia de Question y asignarle los valores necesarios
        $question = new Question();
        
        // Usar Reflection para asignar el ID
        $reflection = new \ReflectionClass($question);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($question, 1);
    
        // Asignar otros valores necesarios
        $question->setQuestionId(123)
            ->setTitle('Sample question from DB')
            ->setTags('php,symfony')
            ->setLink('https://example.com')
            ->setCreationDate(new \DateTime())
            ->setInAnswered(true)
            ->setAnswerCount(2)
            ->setScore(10);
    
        $mockedQuestions = [$question];
    
        // Configurar el mock para devolver la pregunta simulada
        $this->questionService->method('findQuestionsByTag')
            ->willReturn($mockedQuestions);
    
        // Configurar el servicio de API para que no se llame
        $this->stackOverflowService->expects($this->never())
            ->method('fetchQuestions');
    
        // Crear la solicitud
        $request = new Request(['tagged' => 'php']);
        $response = $this->controller->getQuestions($request);
    
        // Comprobar el código de estado de la respuesta
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
    
        // Comprobar el origen 'db'
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('db', $responseData['source']);
        $this->assertNotEmpty($responseData['data']);
    }
    
}
