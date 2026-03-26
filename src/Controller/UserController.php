<?php

namespace App\Controller;

use App\Form\ProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

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
                           EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $userForm = $this->createForm(ProfileType::class, $user);
        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {

            /**
             * @var UploadedFile $file
             */
            $file = $userForm->get('image')->getData();
            if($file){
                $newFileName = $user->getUsername() .'-'.uniqid(). '.' . $file->guessExtension();
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/assets/images/profileImages';
                $file->move($uploadsDir, $newFileName);
                $user->setImage($newFileName);
            }


            $plainPassword = $userForm->get('password')->getData();
            if ($plainPassword){
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour!');
            return $this->redirectToRoute('user_update');
        }

        return $this->render('user/update.html.twig', ['ProfileType' => $userForm]);

    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        if (!$user){
            throw $this->createNotFoundException();
        }
        return $this->render('user/show.html.twig', ['user' => $user]);
    }

}


