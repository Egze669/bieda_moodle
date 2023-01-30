<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Task;
use App\Entity\User;
use App\Form\AnswerType;
use App\Form\TaskType;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StudentController extends AbstractController
{
    #[Route('/student/upload', name: 'upload_answer')]
    public function uploadTask(Request $request,){

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

    #[Route('/student/answers', name: 'student_answers')]
    public function studentAnswersViewer(ManagerRegistry $doctrine): Response
    {
        $teacher = $doctrine->getRepository(User::class)->find($this->getUser());
        return $this->render('student/answersViewer.html.twig', [
            'answers' => $teacher->getAnswers()
        ]);

    }

    /**
     * @throws FilesystemException
     */
    #[Route('/student/task/{idTask}', name: 'student_answer')]
    public function studentTaskAnswer(Request $request, ManagerRegistry $doctrine,string $idTask,FilesystemOperator $answerStorage): Response
    {
        $em = $doctrine->getManager();
        $task = $doctrine->getRepository(Task::class)->find($idTask);
        $existingAnswer = $doctrine->getRepository(Answer::class)->findOneBy(array('task'=>$task, 'autor'=>$this->getUser()));
        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $path =  '/uploads/'.
                $this->getUser()->getEmail().
                '/'.
                $task->getTitle().
                '.'.
                $request->files->get('answer')['content']->guessExtension();

            if(!$existingAnswer){
                $answerStorage->write(
                    $path,
                    file_get_contents($form['content']->getData())
                );
                $answer->setContent($path);
                $answer->setAutor($this->getUser());
                $answer->setTask($task);
                $answer->setSubmitDate(new \DateTime());
                $this->getUser()->addAnswer($answer);
                $task->addAnswer($answer);

                $em->persist($answer);
                $em->flush();

            }

        }




        return $this->render('student/answerTask.html.twig', [
                'task'=>$task,
                'files'=>$form['content']->getData(),
                'existing_answer'=>$existingAnswer,
                'form'=>$form->createView()
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
