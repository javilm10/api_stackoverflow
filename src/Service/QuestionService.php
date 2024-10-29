<?php

namespace App\Service;

use App\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;

class QuestionService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findQuestionsByTag(string $tagged, ?string $fromDate = null, ?string $toDate = null): array
    {
        $query = $this->entityManager->getRepository(Question::class)->createQueryBuilder('q')
            ->select('q')
            ->where('q.tags LIKE :tagged')
            ->setParameter('tagged', '%' . $tagged . '%');

        if ($fromDate) {
            $query->andWhere('q.creation_date >= :fromDate')
                ->setParameter('fromDate', new \DateTime($fromDate));
        }

        if ($toDate) {
            $query->andWhere('q.creation_date <= :toDate')
                ->setParameter('toDate', new \DateTime($toDate));
        }

        return $query->getQuery()->getResult();
    }

    public function saveQuestions(array $questionsData): void
    {
        foreach ($questionsData as $questionData) {
            $existingQuestion = $this->entityManager->getRepository(Question::class)
                ->findOneBy(['question_id' => $questionData['question_id']]);

            if ($existingQuestion) {
                // Actualizar campos existentes
                $existingQuestion->setTitle($questionData['title']);
                $existingQuestion->setTags(implode(',', $questionData['tags']));
                $existingQuestion->setLink($questionData['link']);
                $existingQuestion->setCreationDate(new \DateTime('@' . $questionData['creation_date']));
                $existingQuestion->setInAnswered($questionData['is_answered']);
                $existingQuestion->setAnswerCount($questionData['answer_count']);
                $existingQuestion->setScore($questionData['score']);
            } else {
                // Crear nueva entidad
                $question = new Question();
                $question->setQuestionId($questionData['question_id']);
                $question->setTitle($questionData['title']);
                $question->setTags(implode(',', $questionData['tags']));
                $question->setLink($questionData['link']);
                $question->setCreationDate(new \DateTime('@' . $questionData['creation_date']));
                $question->setInAnswered($questionData['is_answered']);
                $question->setAnswerCount($questionData['answer_count']);
                $question->setScore($questionData['score']);

                $this->entityManager->persist($question);
            }
        }

        $this->entityManager->flush();
    }
}
