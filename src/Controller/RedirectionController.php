<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RedirectionController extends AbstractController
{
    #[Route('/redirect', name: 'app_redirect')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if(in_array('ROLE_TEACHER',$this->getUser()->getRoles()))
            return $this->redirect($this->generateUrl('app_teacher'));
        elseif (in_array('ROLE_STUDENT',$this->getUser()->getRoles()))
            return $this->redirect($this->generateUrl('teacher_viewer'));
        else
        return $this->render('login/addTask.html.twig', [
        ]);
    }
}
