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
        $id = $user->getId();
        if (!$user) {
            throw $this->createNotFoundException('Profile not found');
//            return $this->redirectToRoute('main_home');
        }
        $userForm = $this->createForm(ProfileType::class, $user);
        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour!');
            return $this->redirectToRoute('user_update', ['id' => $id, 'user' => $user]);
        }

//        return $this->render('user/update.html.twig', [
//            'controller_name' => 'UserController',
//        ]);
        return $this->render('user/update.html.twig', ['id' => $id, 'ProfileType' => $userForm]);

    }

}

//#[Route(path: '/profile/update', name: 'profile_update', methods: ['POST', 'GET'])]
//    public function profileUpdate(
//    int                    $id,
//    Request                $request,
//    EntityManagerInterface $entityManager
//): Response
//{
//    $user = $this->getUser();
//    if(!$user){
//        throw $this->createNotFoundException('Profile not found');
////            return $this->redirectToRoute('main_home');
//    }
//    $userForm = $this->createForm(ProfileType::class, $user);
//    $userForm->handleRequest($request);
//    if ($userForm->isSubmitted() && $userForm->isValid()) {
//        $entityManager->persist($user);
//        $entityManager->flush();
//        $this->addFlash('success', 'Profil mis à jour!');
//        return $this->redirectToRoute('profile_update', ['id' => $id]);
//    }
//
//    return $this->render('user/update.html.twig', ['id' => $id, 'form' => $userForm]);
//}
