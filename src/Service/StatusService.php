<?php

namespace App\Service;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;

class StatusService
{
    private EventRepository $eventRepository;
    private StatusRepository $statusRepository;

    private EntityManagerInterface $entityManagerInterface;

    public function __construct(
        EventRepository        $eventRepository,
        StatusRepository       $statusRepository,
        EntityManagerInterface $entityManagerInterface

    )
    {
        $this->eventRepository = $eventRepository;
        $this->statusRepository = $statusRepository;
        $this->entityManagerInterface = $entityManagerInterface;
    }


    public function updateAllEventsStatus(): void
    {


        $events = $this->eventRepository->findAllEventsByStatusToChange();


        $closedStatus = $this->statusRepository->findOneBy([
            'description' => 'Clôturée'
        ]);

        $openStatus = $this->statusRepository->findOneBy([
            'description' => 'Ouverte'
        ]);

        $inProgressStatus = $this->statusRepository->findOneBy([
            'description' => 'En cours'
        ]);

        $finishedStatus = $this->statusRepository->findOneBy([
            'description' => 'Terminée'
        ]);

        $historizedStatus = $this->statusRepository->findOneBy([
            'description' => 'Historisée'
        ]);
        $today = new \DateTime('now');
        $historizingDate = new \DateTimeImmutable('-1 month');
        foreach ($events as $event) {
            $nbParticipants = $event->getParticipants()->count();

            //historisation
            if ($event->getEndDate() <= $historizingDate) {
                $event->setStatus($historizedStatus);
            }
            //statut "Terminée"
            elseif ($event->getEndDate() <= $today) {
                $event->setStatus($finishedStatus);
            }
            //statut "En cours"
            elseif ($event->getEndDate() > $today && $event->getBeginDateEvent() < $today) {
                $event->setStatus($inProgressStatus);
            }
            else {
                //statut ouvert
                if ($nbParticipants < $event->getRegistrationMaxNb() && $event->getLimitDateRegistration() > $today) {
                    $event->setStatus($openStatus);
                }
                //Clôture d'un event
                elseif ($nbParticipants >= $event->getRegistrationMaxNb() || $event->getLimitDateRegistration() < $today) {
                    $event->setStatus($closedStatus);
                }
            }
        }
        $this->entityManagerInterface->flush();
    }

}
