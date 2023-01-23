<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\User;
use App\Form\AnswerType;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StudentController extends AbstractController
{
    #[Route('/student/upload', name: 'upload_answer')]
    public function uploadTask(Request $request){

    }
    #[Route('/student', name: 'teacher_viewer')]
    public function teacherViewer(ManagerRegistry $doctrine): Response
    {
        $teachersArray = $doctrine->getRepository(User::class)->findAllTeachers();
        return $this->render('student/teacherViewer.html.twig', [
            'teachersArray'=>$teachersArray,
        ]);

    }
    #[Route('/student/teacher/{email}', name: 'teacher_tasks')]
    public function studentTaskViewer(ManagerRegistry $doctrine,string $email): Response
    {
        $teacher = $doctrine->getRepository(User::class)->findOneBy(['email' => $email]);
        return $this->render('student/teacherTasks.html.twig', [
            'tasks' => $teacher->getTasks()
        ]);

    }
    #[Route('/student/task/{idTask}', name: 'student_answer')]
    public function studentTaskAnswer(ManagerRegistry $doctrine,string $idTask): Response
    {

        return $this->render('student/answerTask.html.twig', [

        ]);

    }

//    #[Route('/student', name: 'app_student')]
//    public function index(Request $request): Response
//    {
//        $task = new Answer();
//        $form = $this->createForm(AnswerType::class,$task);
//        $form->getData();
//
//        $form->handleRequest($request);
//        if($form->isSubmitted()&&$form->isValid()){
//            var_dump($form->getData());
//        }
//        return $this->render('teacher/addTask.html.twig', [
//            'controller_name' => 'StudentController',
//            'form' => $form->createView(),
//        ]);
//
//    }
}
