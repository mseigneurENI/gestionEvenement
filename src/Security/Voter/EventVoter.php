<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class EventVoter extends Voter
{
    public const EDIT = 'EVENT_EDIT';
    public const VIEW = 'EVENT_VIEW';
    public const DELETE = 'EVENT_DELETE';
    public const REGISTER = 'EVENT_REGISTER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE, self::REGISTER])
            && $subject instanceof \App\Entity\Event;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            $vote?->addReason('The user must be logged in to access this resource.');

            return false;
        }

        $event = $subject;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                if($user === $event->getOrganiser()){// logic to determine if the user can EDIT
                return true;// return true or false
                }
                break;

            case self::VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                break;

            case self::DELETE:
                if($user === $event->getOrganiser()){
                    return true;
                }
                break;

            case self::REGISTER:
                $status = $event->getStatus() ;
                $limitDate = $event->getlimitDateRegistration();

                if($status && $status->getDescription() === 'Ouverte' && $limitDate >= new \DateTime()){
                    return true;
                }
                break;

        }

        return false;
    }
}
