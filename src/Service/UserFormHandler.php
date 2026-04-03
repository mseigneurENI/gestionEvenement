<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFormHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function createCompleteUser(FormInterface $form, User $user): User
    {

        /**
         * @var UploadedFile $file
         */
        $file = $form->get('image')->getData();
        if ($file) {
            $newFileName = $user->getUsername() . '-' . uniqid() . '.' . $file->guessExtension();
             $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/assets/images/profileImages';
            $file->move($uploadsDir, $newFileName);
            $user->setImage($newFileName);
        }


        $plainPassword = $form->get('password')->getData();
        if ($plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        } else {
            $year = (new \DateTime())->format('Y');
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getFirstname() . '.' . $user->getLastname() . '@' . $year));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }

}
