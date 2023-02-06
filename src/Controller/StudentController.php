<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Task;
use App\Entity\User;
use App\Form\AnswerType;
use App\Form\TaskType;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StudentController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/student', name: 'teacher_viewer')]
    public function teacherViewer(ManagerRegistry $doctrine): Response
    {
        /** @var UserRepository $teachersRepo */
        $teachersRepo = $doctrine->getRepository(User::class);
        $teachersArray = $teachersRepo->findAllTeachers();
        return $this->render('student/teacherViewer.html.twig', [
            'teachersArray'=>$teachersArray,
        ]);

    }
    #[Route('/student/teacher/{email}', name: 'teacher_tasks')]
    public function studentTaskViewer(ManagerRegistry $doctrine,string $email): Response
    {
        /** @var User $teacher */
        $teacher = $doctrine->getRepository(User::class)->findOneBy(['email' => $email]);
        /** @var array $task */
        $task = $teacher->getTasks();
        return $this->render('student/teacherTasks.html.twig', [
            'tasks' => $task
        ]);

    }

    #[Route('/student/answers', name: 'student_answers')]
    public function studentAnswersViewer(ManagerRegistry $doctrine): Response
    {
        /** @var User $teacher */
        $teacher = $doctrine->getRepository(User::class)->find($this->getUser());
        /** @var array $answers */
        $answers = $teacher->getAnswers();
        return $this->render('student/answersViewer.html.twig', [
            'answers' => $answers
        ]);

    }

    /**
     * @throws FilesystemException
     */
    #[Route('/student/task/{idTask}', name: 'student_answer')]
    public function studentTaskAnswer(Request $request, ManagerRegistry $doctrine,string $idTask,FilesystemOperator $answerStorage): Response
    {
        $em = $doctrine->getManager();
        /** @var Task $task */
        $task = $doctrine->getRepository(Task::class)->find($idTask);
        /** @var User $user */
        $user = $this->getUser();
        /** @var string $title */
        $title = $task->getTitle();
        /** @var string $email */
        $email = $user->getEmail();
        $existingAnswer = $doctrine->getRepository(Answer::class)->findOneBy(array('task'=>$task, 'autor'=>$this->getUser()));
        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var array $file */
            $file = $request->files->get('answer');
            /** @var UploadedFile $content */
            $content = $file['content'];
            /** @var string $extension */
            $extension = $content->guessExtension();
            $path =  '/uploads/'.
                $email.
                '/'.
                $title.
                '.'.
                $extension;

            if(!$existingAnswer){
                /** @var User $user */
                $user = $this->getUser();
                $answerStorage->write(
                    $path,
                    file_get_contents((string)$form['content']->getData())
                );
                $answer->setContent($path);
                $answer->setAutor($user);
                $answer->setTask($task);
                $answer->setSubmitDate(new \DateTime());
                $user->addAnswer($answer);
                $task->addAnswer($answer);

                $em->persist($answer);
                $em->flush();

                return $this->redirect($request->getUri());
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
