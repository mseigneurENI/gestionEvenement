<?php

namespace App\Controller;

use App\Form\ProfileType;
use App\Repository\UserRepository;
use Cassandra\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {

    }

    #[Route(path: '/profile/update/{id}', name: 'profile_update', methods: ['POST', 'GET'])]
    public function profileUpdate(
        int                    $id,
        UserRepository         $userRepository,
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $userRepository->find($id);
        $userForm = $this->createForm(ProfileType::class, $user);
        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour!');
            return $this->redirectToRoute('profile_update', ['id' => $id]);
        }

        return $this->render('Security/updateProfile.html.twig', ['form' => $userForm]);
    }

}
