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
    #[Route('/', name: 'app_redirect')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var array $userRoles */
        if(is_null($user)){
            return $this->redirectToRoute('app_login');
        }
        $userRoles = $user->getRoles();
        if(in_array('ROLE_TEACHER',$userRoles))
            return $this->redirect($this->generateUrl('app_teacher'));
        elseif (in_array('ROLE_STUDENT',$userRoles))
            return $this->redirect($this->generateUrl('teacher_viewer'));

    }
}
