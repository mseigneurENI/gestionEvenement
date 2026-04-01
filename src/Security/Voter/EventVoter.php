<?php

namespace App\Security\Voter;

use Symfony\Bundle\SecurityBundle\Security;
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
            $vote?->addReason('Vous devez être connecté·e pour accéder à cette page.');

            return false;
        }

        $event = $subject;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                if($user === $event->getOrganiser()){// si l'utilisateur est l'organisateur de l'événement, il peut EDIT puisqu'on retourne true
                return true;
                }
                break;

            case self::VIEW:
                    // quand on arrive ici ça veut dire que l'utilisateur est déjà connecté, donc il suffit de retourner true.
                return true;

            case self::DELETE:
                if($user === $event->getOrganiser()){
                    return true;
                }
                break;

            case self::REGISTER:
                $status = $event->getStatus() ;
                $limitDate = $event->getlimitDateRegistration();

                if($status && $status->getDescription() === 'Ouverte' && $limitDate >= new \DateTime() ){
                    return true;
                }
                break;

        }

        return false;
    }
}
