<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\CampusRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    public function update(Request                $request,
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
            if ($file) {
                $newFileName = $user->getUsername() . '-' . uniqid() . '.' . $file->guessExtension();
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/assets/images/profileImages';
                $file->move($uploadsDir, $newFileName);
                $user->setImage($newFileName);
            }


            $plainPassword = $userForm->get('password')->getData();
            if ($plainPassword) {
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
        if (!$user) {
            throw $this->createNotFoundException();
        }
        return $this->render('user/show.html.twig', ['user' => $user]);
    }

    #[Route('/create', name: 'create', methods: ['POST', 'GET'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $user->setActive(true);
        $year = (new \DateTime())->format('Y');
        //mot de passe par défaut s'il n'est pas rempli par l'admin
//        $user->setPassword($passwordHasher->hashPassword($user, $user->getFirstname().'.'.$user->getLastname().'@'.$year));
        $user->setPassword($passwordHasher->hashPassword($user, '123456'));
        $userForm = $this->createForm(ProfileType::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {

            /**
             * @var UploadedFile $file
             */
            $file = $userForm->get('image')->getData();
            if ($file) {
                $newFileName = $user->getUsername() . '-' . uniqid() . '.' . $file->guessExtension();
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/assets/images/profileImages';
                $file->move($uploadsDir, $newFileName);
                $user->setImage($newFileName);
            }


            $plainPassword = $userForm->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            } else {
                $user->setPassword($passwordHasher->hashPassword($user, $user->getFirstname() . '.' . $user->getLastname() . '@' . $year));

            }

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Nouvel utilisateur créé');
            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }
        return $this->render('user/create.html.twig', ['userForm' => $userForm]);
    }

//    #[IsGranted('EVENT_DELETE', 'event', 'Vous ne pouvez pas supprimer une sortie que vous n\'avez pas créée.')]
    #[Route('/{id}/delete', name: 'delete', methods: ['POST','GET'])]
    public function delete(
        User                        $user,
        EntityManagerInterface      $entityManager
    ): Response
    {
        if (!$user) {
            throw $this->createNotFoundException('user does not exist');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Suppression réussie');

        return $this->redirectToRoute('user_listUser');
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/user/{id}/activate', name: 'activate', methods: ['GET','POST'])]
    public function activate(
        User                            $user,
        EntityManagerInterface          $entityManagerInterface
    ): Response
    {
        $user->setActive(true);
        $entityManagerInterface->flush();

        $this->addFlash('success', 'user activated.');
        return $this->redirectToRoute('user_listUser');
    }
    #[Route('/listUser', name: 'listUser')]
    public function showUser(UserRepository $userRepository)
    {
        $listUser = $userRepository->findAllByAlphabeticalOrder();

        return $this->render('user/listUser.html.twig', [
            'listUser' => $listUser
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/user/{id}/deactivate', name: 'deactivate', methods: ['GET','POST'])]
    public function deactivate(
        User                        $user,
        EntityManagerInterface      $entityManagerInterface):
    Response
    {
        $user->setActive(false);
        $entityManagerInterface->flush();

        $this->addFlash('success', 'user deactivated.');
        return $this->redirectToRoute('user_listUser');
    }


    #[Route("/import", name: 'import', methods: ['POST', 'GET'])]
    public function importCsv(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, CampusRepository $campusRepository): Response
    {
        if (!$this->getUser()->getRoles('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException('Vous n\'avez pas les droits nécessaires à cette action');
        }
        $errors = [];
        $created = 0;
        if ($request->isMethod('POST')) {
            /**
             * @var UploadedFile $csvFile
             */
            $csvFile = $request->files->get('csvFile');
            if ($csvFile) {
                try {
                    $handle = fopen($csvFile->getPathname(), "r");
                    if ($handle) {
                        fgetcsv($handle, 0, ';');
                        while (($row = fgetcsv($handle, 0, ';')) !== false) {
                            $newPseudo = $row[0];
                            $newEmail = $row[3];
                            if ($userRepository->findOneBy(['username' => $newPseudo]) || $userRepository->findOneBy(['email' => $newPseudo])) {
                                $errors[] = "Doublon ignoré : $newPseudo ($newEmail)";
                                continue;
                            }

                            $user = new User();
                            $user->setUsername($row[0]);
                            $user->setLastname($row[1]);
                            $user->setFirstname($row[2]);
                            $user->setEmail($row[3]);
                            if ($row[4]) {
                                $user->setPhoneNb($row[4]);
                            } else {
                                $user->setPhoneNb(null);
                            }
                            $user->setRoles(['ROLE_USER']);
                            $user->setActive(true);
                            $campus = $campusRepository->findOneBy(['name' => 'Saint-Herblain']);
                            if ($campus) {
                                $user->setCampus($campus);
                            } else {
                                $pseudo = $user->getUsername();
                                $errors[] = "Campus non attribué pour $pseudo";
                            }
//                            $user->setPassword($passwordHasher->hashPassword($user, '123456'));
                            $year = (new \DateTime())->format('Y');
                            $user->setPassword($passwordHasher->hashPassword($user, $user->getFirstname() . '.' . $user->getLastname() . '@' . $year));
                            $entityManager->persist($user);
                            $created++;
                        }
                        fclose($handle);
                        $entityManager->flush();
                    }
                } catch (\Exception $e) {
                    $errors[] = "Erreur fichier : " . $e->getMessage();
                }
                if ($created > 0) {
                    $this->addFlash('success', "$created utilisateurs créés avec succès!");
                }
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
            }
        }
        return $this->render('user/import.html.twig');
    }

}


