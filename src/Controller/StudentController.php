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
    #[Route('/student/task', name: 'task_viewer')]
    public function studentTaskViewer(Request $request): Response
    {
        $task = new Answer();
        $form = $this->createForm(AnswerType::class,$task);
        $form->getData();

        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid()){
            var_dump($form->getData());
        }
        return $this->render('student/tasks.html.twig', [
            'controller_name' => 'StudentController',
            'form' => $form->createView(),
        ]);

    }
    #[Route('/student', name: 'teacher_viewer')]
    public function teacherViewer(ManagerRegistry $doctrine): Response
    {
        $teachersArray = $doctrine->getRepository(User::class)->findAllTeachers();
        return $this->render('student/teacherViewer.html.twig', [
            'teachersArray'=>$teachersArray,
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
//        return $this->render('teacher/index.html.twig', [
//            'controller_name' => 'StudentController',
//            'form' => $form->createView(),
//        ]);
//
//    }
}
