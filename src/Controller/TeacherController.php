<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/teacher/delete{idTask}', name: 'teacher_task_delete')]
    public function teacherTaskDelete(Request $request, ManagerRegistry $doctrine, int $idTask): Response
    {
        $em = $doctrine->getManager();
        $task = $doctrine->getRepository(Task::class)->find($idTask);
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
