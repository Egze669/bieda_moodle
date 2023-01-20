<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeacherController extends AbstractController
{

    #[Route('/teacher/upload', name: 'upload_task')]
    public function uploadTask(Request $request){

    }
    #[Route('/teacher', name: 'app_teacher')]
    public function index(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class,$task);
        $form->getData();

        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid()){
            var_dump('debil');
        }
        return $this->render('teacher/index.html.twig', [
            'controller_name' => 'TeacherController',
            'form' => $form->createView(),
        ]);
    }
}
