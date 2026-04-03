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

    public function managePasswordAttribution(FormInterface $form, User $user): User
    {
        $plainPassword = $form->get('password')->getData();
        if ($plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        } else {
            $year = (new \DateTime())->format('Y');
            $plainPassword = $user->getFirstname() . '.' . $user->getLastname() . '@' . $year;
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }
        return $user;
    }

}
