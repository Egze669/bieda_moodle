<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class TeacherController extends AbstractController
{

    #[Route('/teacher/add', name: 'teacher_task_add')]
    public function teacherTaskAdd(Request $request, ManagerRegistry $doctrine): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setAutor($this->getUser());
            $this->getUser()->addTask($task);
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
        $task = $doctrine->getRepository(Task::class)->find($idTask);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
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
     */
    #[Route('/teacher/delete{idTask}', name: 'teacher_task_delete')]
    public function teacherTaskDelete(Request $request, ManagerRegistry $doctrine, int $idTask,FilesystemOperator $answerStorage): Response
    {
        $em = $doctrine->getManager();
        $task = $doctrine->getRepository(Task::class)->find($idTask);
        $answers = $task->getAnswers();
        foreach ($answers as $answer){
            $answerStorage->delete($answer->getContent());
            $em->remove($answer);
        }
        $this->getUser()->removeTask($task);
        $em->remove($task);
        $em->flush();
        return $this->redirectToRoute('app_teacher');
    }

    #[Route('/teacher/task/{idTask}/answers', name: 'teacher_task_answers')]
    public function teacherTaskAnswers(Request $request, ManagerRegistry $doctrine, int $idTask): Response
    {
        $task = $doctrine->getRepository(Task::class)->find($idTask);
        return $this->render('teacher/taskAnswers.html.twig', [
            'answers' => $task->getAnswers()
        ]);
    }

    /**
     * @throws FilesystemException
     */
    #[Route('/download/{idAnswer}', name: 'download_answer')]
    public function downloadAnswer(ManagerRegistry $doctrine,string $idAnswer,FilesystemOperator $answerStorage): StreamedResponse
    {
        $answer = $doctrine->getRepository(Answer::class)->find($idAnswer);
        $pathParts = pathinfo($answer->getContent());

        $response = new StreamedResponse(function() use ($answerStorage, $answer) {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = $answerStorage->readStream($answer->getContent());
            stream_copy_to_stream($fileStream, $outputStream);
        });
        $response->headers->set('Content-Type', $pathParts['extension']);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $answer->getTask()->getTitle()."_".$answer->getSubmitDate()->format('Y-m-d')."_".$answer->getAutor()->getName()."_".$answer->getAutor()->getSurname()
        );
        $response->headers->set('Content-Disposition', $disposition);
        return $response;

    }
    #[Route('/grade/{idAnswer}', name: 'grade_answer')]
    public function gradeAnswer(Request $request, ManagerRegistry $doctrine,string $idAnswer,FilesystemOperator $answerStorage)
    {
        $answer = $doctrine->getRepository(Answer::class)->find($idAnswer);
        $answer->setGrade($request->get('grade'));

        $em = $doctrine->getManager();
        $em->persist($answer);
        $em->flush();
        return $this->render('teacher/taskAnswers.html.twig', [
            'answers' => $answer->getTask()->getAnswers()
        ]);
    }


    #[Route('/teacher', name: 'app_teacher')]
    public function index(ManagerRegistry $doctrine): Response
    {
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
