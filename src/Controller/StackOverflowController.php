<?php

namespace App\Controller;

use App\Service\ApiErrorHandler;
use App\Service\QuestionService;
use App\Service\StackOverflowService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StackOverflowController extends AbstractController
{
    private StackOverflowService $stackOverflowService;
    private QuestionService $questionService;
    private ApiErrorHandler $apiErrorHandler;

    public function __construct(StackOverflowService $stackOverflowService, QuestionService $questionService, ApiErrorHandler $apiErrorHandler)
    {
        $this->stackOverflowService = $stackOverflowService;
        $this->questionService = $questionService;
        $this->apiErrorHandler = $apiErrorHandler;
    }

    #[Route('/api/questions', name: 'api_questions', methods: ['GET'])]
    public function getQuestions(Request $request): JsonResponse
    {
        // Obtener los filtros del request
        $tagged = $request->query->get('tagged');
        $fromDate = $request->query->get('fromdate');
        $toDate = $request->query->get('todate');
        $forceRefresh = $request->query->get('forceRefresh');

        // Validar que el parámetro 'tagged' es obligatorio
        if (!$tagged) {
            return new JsonResponse(['error' => 'The "tagged" parameter is required.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            if ($forceRefresh != "true") {
                // Consultar las preguntas desde la base de datos
                $existingQuestions = $this->questionService->findQuestionsByTag($tagged, $fromDate, $toDate);
            } else {
                $existingQuestions = [];
            }

            // Comprobar si existen preguntas
            if (count($existingQuestions) > 0) {
                return $this->createJsonResponse($existingQuestions, 'db');
            } else {
                // Si no hay preguntas en la base de datos, hacer la llamada a la API
                $questionsData = $this->stackOverflowService->fetchQuestions(
                    $tagged,
                    $fromDate ? new \DateTime($fromDate) : null,
                    $toDate ? new \DateTime($toDate) : null
                );

                // Guardar o actualizar las preguntas en la base de datos
                $this->questionService->saveQuestions($questionsData);

                // Volver a consultar las preguntas desde la base de datos después de guardarlas
                $existingQuestions = $this->questionService->findQuestionsByTag($tagged, $fromDate, $toDate);
                return $this->createJsonResponse($existingQuestions, 'api');
            }
        } catch (\Exception $e) {
            $errorResponse = $this->apiErrorHandler->handle($e);
            return new JsonResponse(['error' => $errorResponse['message']], $errorResponse['status']);
        }
    }

    private function createJsonResponse(array $questions, string $source): JsonResponse
    {
        // Convertir las entidades a un array
        $questionsArray = array_map(function ($question) {
            return [
                'id' => $question->getId(),
                'question_id' => $question->getQuestionId(),
                'title' => $question->getTitle(),
                'tags' => $question->getTags(),
                'link' => $question->getLink(),
                'creation_date' => $question->getCreationDate()->format('Y-m-d H:i:s'),
                'in_answered' => $question->isInAnswered(),
                'answer_count' => $question->getAnswerCount(),
                'score' => $question->getScore(),
            ];
        }, $questions);

        return new JsonResponse([
            'source' => $source,
            'data' => $questionsArray
        ], JsonResponse::HTTP_OK);
    }
}
