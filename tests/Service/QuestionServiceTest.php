<?php

namespace App\Tests\Service;

use App\Entity\Question;
use App\Service\QuestionService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\Query;

class QuestionServiceTest extends TestCase
{
    private QuestionService $questionService;
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;
    private QueryBuilder $queryBuilder;
    private Query $query;

    protected function setUp(): void
    {
        // Crear un mock del EntityManager
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Crear un mock del repositorio
        $this->repository = $this->createMock(EntityRepository::class);

        // Crear un mock del QueryBuilder
        $this->queryBuilder = $this->createMock(QueryBuilder::class);

        // Configurar el mock del repositorio para que devuelva el QueryBuilder
        $this->entityManager->method('getRepository')->willReturn($this->repository);
        
        // Instanciar el servicio con el EntityManager mockeado
        $this->questionService = new QuestionService($this->entityManager);
        
        $this->query = $this->createMock(Query::class);
    }

    public function testFindQuestionsByTagWithoutDate(): void
    {
        // Datos de prueba
        $tagged = 'php';
        $expectedQuestions = [
            (new Question())->setQuestionId(101)->setTitle('Question 1')->setTags('php')->setLink('http://example.com/1')->setCreationDate(new \DateTime())->setInAnswered(true)->setAnswerCount(2)->setScore(10),
            (new Question())->setQuestionId(102)->setTitle('Question 2')->setTags('php')->setLink('http://example.com/2')->setCreationDate(new \DateTime())->setInAnswered(false)->setAnswerCount(1)->setScore(5),
        ];

        // Configurar el QueryBuilder
        $this->repository->method('createQueryBuilder')->willReturn($this->queryBuilder);
        
        // Configurar los métodos del QueryBuilder
        $this->queryBuilder->method('select')->willReturnSelf(); // Retorna sí mismo
        $this->queryBuilder->method('where')->willReturnSelf(); // Retorna sí mismo
        $this->queryBuilder->method('setParameter')->willReturnSelf(); // Retorna sí mismo
        $this->queryBuilder->method('getQuery')->willReturn($this->query); // Retorna sí mismo
        $this->query->method('getResult')->willReturn($expectedQuestions); // Devuelve las preguntas esperadas

        // Ejecutar el método
        $actualQuestions = $this->questionService->findQuestionsByTag($tagged);

        // Verificar el resultado
        $this->assertSame($expectedQuestions, $actualQuestions);
    }

    public function testFindQuestionsByTagWithDate(): void
    {
        // Datos de prueba
        $tagged = 'php';
        $fromDate = '2023-01-01';
        $toDate = '2023-12-31';
        $expectedQuestions = [
            (new Question())->setQuestionId(101)->setTitle('Question 1')->setTags('php')->setLink('http://example.com/1')->setCreationDate(new \DateTime())->setInAnswered(true)->setAnswerCount(2)->setScore(10),
        ];

        // Configurar el QueryBuilder
        $this->repository->method('createQueryBuilder')->willReturn($this->queryBuilder);
        
        // Configurar los métodos del QueryBuilder
        $this->queryBuilder->method('select')->willReturnSelf();
        $this->queryBuilder->method('where')->willReturnSelf();
        $this->queryBuilder->method('setParameter')->willReturnSelf();
        $this->queryBuilder->method('andWhere')->willReturnSelf();
        $this->queryBuilder->method('getQuery')->willReturn($this->query);
        $this->query->method('getResult')->willReturn($expectedQuestions);

        // Ejecutar el método
        $actualQuestions = $this->questionService->findQuestionsByTag($tagged, $fromDate, $toDate);

        // Verificar el resultado
        $this->assertSame($expectedQuestions, $actualQuestions);
    }

    public function testSaveQuestions(): void
    {
        // Datos de prueba
        $questionsData = [
            [
                'question_id' => 101,
                'title' => 'Question 1',
                'tags' => ['php', 'symfony'],
                'link' => 'http://example.com/1',
                'creation_date' => time(),
                'is_answered' => true,
                'answer_count' => 2,
                'score' => 10,
            ],
        ];

        // Mock del repositorio para el método findOneBy
        $this->repository->method('findOneBy')->willReturn(null);

        // Configurar el mock del EntityManager para persistir la entidad
        $this->entityManager->expects($this->exactly(1))
            ->method('persist');

        $this->entityManager->expects($this->exactly(1))
            ->method('flush');

        // Ejecutar el método
        $this->questionService->saveQuestions($questionsData);
    }
}
