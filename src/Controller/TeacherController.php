<?php

namespace App\Controller;

use App\Dto\TaskDTO;
use App\Entity\Answer;
use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\Types\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\isNull;

class TeacherController extends AbstractController
{

    #[Route('/teacher/add', name: 'teacher_task_add')]
    public function teacherTaskAdd(Request $request, ManagerRegistry $doctrine): Response
    {
        $taskDTO = new TaskDTO();
        $form = $this->createForm(TaskType::class, $taskDTO);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $task = new Task($taskDTO->title, $taskDTO->description, $user, $taskDTO->activationDate, $taskDTO->deactivationDate);
            $user->addTask($task);
            $em = $doctrine->getManager();
            $em->persist($task);
            $em->flush();

            return $this->redirect($this->generateUrl('app_teacher'));
        }
        return $this->render('teacher/addTask.html.twig', [
            'controller_name' => 'TeacherController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/teacher/update{idTask}', name: 'teacher_task_update')]
    public function teacherTaskUpdate(Request $request, ManagerRegistry $doctrine, int $idTask): Response
    {
        /** @var Task $task */
        $task = $doctrine->getRepository(Task::class)->find($idTask);
        $taskDTO = new TaskDTO();
            $taskDTO->updateTask($task);

            $form = $this->createForm(TaskType::class, $taskDTO);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var string $title */
                $title = $taskDTO->title;
                /** @var string $description */
                $description = $taskDTO->description;
                /** @var \DateTimeInterface $activationDate */
                $activationDate = $taskDTO->activationDate;
                /** @var \DateTimeInterface $deactivationDate */
                $deactivationDate = $taskDTO->deactivationDate;
                $em = $doctrine->getManager();
                $task->setTitle($title);
                $task->setDescription($description);
                $task->setActivationdate($activationDate);
                $task->setDeactivationdate($deactivationDate);
                $em->persist($task);
                $em->flush();

                return $this->redirect($this->generateUrl('teacher_task', ['id' => $idTask]));
            }

            return $this->render('teacher/editTask.html.twig', [
                'form' => $form->createView(),
            ]);
    }

    /**
     * @throws FilesystemException
     * @throws ORMException
     */
    #[Route('/teacher/delete{idTask}', name: 'teacher_task_delete')]
    public function teacherTaskDelete(Request $request, ManagerRegistry $doctrine, int $idTask, FilesystemOperator $answerStorage): Response
    {
        /** @var EntityManager $em */
        $em = $doctrine->getManager();
        /** @var Task $task */
        $task = $doctrine->getRepository(Task::class)->find($idTask);
        /** @var array $answers */
        $answers = $task->getAnswers();
        /** @var User $user */
        $user = $this->getUser();
        /** @var Answer $answer */
        foreach ($answers as $answer) {
            /** @var string $content */
            $content = $answer->getContent();
            $answerStorage->delete($content);
            $em->remove($answer);
        }
        $user->removeTask($task);
        $em->remove($task);
        $em->flush();
        return $this->redirectToRoute('app_teacher');
    }

    #[Route('/teacher/task/{idTask}/answers', name: 'teacher_task_answers')]
    public function teacherTaskAnswers(Request $request, ManagerRegistry $doctrine, int $idTask): Response
    {

        /** @var Task $task */
        $task = $doctrine->getRepository(Task::class)->find($idTask);
        /** @var array $answers */
        $answers = $task->getAnswers();
        return $this->render('teacher/taskAnswers.html.twig', [
            'answers' => $answers
        ]);
    }

    /**
     * @throws FilesystemException
     */
    #[Route('/download/{idAnswer}', name: 'download_answer')]
    public function downloadAnswer(ManagerRegistry $doctrine, string $idAnswer, FilesystemOperator $answerStorage): StreamedResponse
    {
        /** @var Answer $answer */
        $answer = $doctrine->getRepository(Answer::class)->find($idAnswer);
        /** @var \DateTimeInterface $submittedDate */
        $submittedDate = $answer->getSubmitDate();
        /** @var string $answerContent */
        $answerContent = $answer->getContent();
        /** @var User $autor */
        $autor = $answer->getAutor();
        /** @var string $name */
        $name = $autor->getName();
        /** @var string $surname */
        $surname = $autor->getSurname();

        /** @var Task $task */
        $task = $answer->getTask();
        /** @var string $title */
        $title = $task->getTitle();

        /** @var array $pathParts */
        $pathParts = pathinfo($answerContent);
        /** @var string $extension */
        $extension = $pathParts['extension'];





        $response = new StreamedResponse(function () use ($answerContent, $answerStorage) {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = $answerStorage->readStream($answerContent);
            stream_copy_to_stream($fileStream, $outputStream);
        });
        $response->headers->set('Content-Type', $extension);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $title . "_" . $submittedDate->format('Y-m-d') . "_" . $name . "_" . $surname
        );
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    #[Route('/grade/{idAnswer}', name: 'grade_answer')]
    public function gradeAnswer(Request $request, ManagerRegistry $doctrine, string $idAnswer, FilesystemOperator $answerStorage): RedirectResponse
    {

        $route = $request->headers->get('referer');
        $answer = $doctrine->getRepository(Answer::class)->find($idAnswer);
        if ($request->query->get('grade')<=5 && $request->query->get('grade')>=1 && !is_null($answer)) {


            $answer->setGrade((float)$request->query->get('grade'));
            $em = $doctrine->getManager();
            $em->persist($answer);
            $em->flush();


        }
        $this->addFlash('error', 'grade is not valid');
        if (!is_null($route)) {
        return new RedirectResponse($route);
        }
        return $this->redirectToRoute('app_teacher');
    }


    #[Route('/teacher', name: 'app_teacher')]
    public function index(ManagerRegistry $doctrine): Response
    {
        /** @var User $teacher */
        $teacher = $this->getUser();
        return $this->render('teacher/tasks.html.twig', [
            'tasks' => $teacher->getTasks()
        ]);

    }

    #[Route('/teacher/task{id}', name: 'teacher_task')]
    public function teacherTask(ManagerRegistry $doctrine, int $id): Response
    {
        $task = $doctrine->getRepository(Task::class)->findOneBy(['id' => $id]);
        return $this->render('teacher/singularTask.html.twig', [
            'task' => $task
        ]);

    }
}
