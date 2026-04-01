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
        EventRepository                     $eventRepository,
        StatusRepository                    $statusRepository,
        EntityManagerInterface              $entityManagerInterface

    ){
        $this->eventRepository = $eventRepository;
        $this->statusRepository = $statusRepository;
        $this->entityManagerInterface = $entityManagerInterface;
    }



    public function updateEventStatus(Event $event): void
    {
        $now = new \DateTime();
        // status archivage
        $oneMonthAgo = (clone $now)->modify('-1 month');
        if ($event->getEndDate() < $oneMonthAgo) {
            $statusArchived = $this->statusRepository->findOneBy(['description' => 'Historisée']);
            if ($statusArchived) {
                $event->setStatus($statusArchived);
                $this->entityManagerInterface->flush();
            }
            return;
        }

       // Status ouverte
        if ($event->getLimitDateRegistration() > $now) {
            $openStatus = $this->statusRepository->findOneBy(['description' => 'Ouverte']);
            if ($openStatus) {
                $event->setStatus($openStatus);
            }
        }

        // Status terminée
        if ($event->getEndDate() < $now) {
            $statusFinished = $this->statusRepository->findOneBy(['description' => 'Terminée']);
            if ($statusFinished) {
                $event->setStatus($statusFinished);
                $this->entityManagerInterface->flush();
            }
            return;
        }

        // en cours
        if ($event->getBeginDateEvent() <= $now) {
            $statusInProgress = $this->statusRepository->findOneBy(['description' => 'En cours']);
            if ($statusInProgress) {
                $event->setStatus($statusInProgress);
                $this->entityManagerInterface->flush();
            }
            return;
        }

        // cloturée
        $nbMax = $event->getParticipants()->count() >= $event->getRegistrationMaxNb();
        $dateLimitPassed = $now > $event->getLimitDateRegistration();

        if (($nbMax || $dateLimitPassed)) {
            $statusClosed = $this->statusRepository->findOneBy(['description' => 'Clôturée']);
            if ($statusClosed) {
                $event->setStatus($statusClosed);
                $this->entityManagerInterface->flush();
            }
        }

    }

    public function updateAllEventsStatus(): void
    {
        $events = $this->eventRepository->findAll();
        foreach ($events as $event) {
            $this->updateEventStatus($event);
        }
    }

}
