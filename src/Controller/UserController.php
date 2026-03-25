<?php

namespace App\Controller;

use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user', name: 'user_')]
final class UserController extends AbstractController
{
//    #[Route("", name: 'show', methods: ['GET'])]
//    public function show(Request $request): Response{
//        $user = $this->getUser();
//        $userForm = $this->createForm(ProfileType::class);
//        $userForm->handleRequest($request);
//
//    }





    #[Route('/update', name: 'update', methods: ['POST', 'GET'])]
    public function update(Request $request,
                           EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $userForm = $this->createForm(ProfileType::class, $user);
        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour!');
            return $this->redirectToRoute('user_update');
        }

        return $this->render('user/update.html.twig', ['ProfileType' => $userForm]);

    }

}


