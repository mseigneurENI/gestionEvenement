<?php

namespace App\Service;

use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;

class ArchiveService
{
//    Archiver les sorties => un service qui tourne à chaque fois qu'n arrive sur la liste
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

    public function archiveEvent(): void
    {
        $now = new \DateTime();
        $oneMonthAgo = (clone $now)->modify('-1 month');
        // mettre en place un flitre pour  gerer la'archivage en function du date
        $oldEvents = $this->eventRepository->createQueryBuilder('e')
            ->where('e.endDate < :oneMonthAgo')
            ->setParameter('oneMonthAgo', $oneMonthAgo)
            ->getQuery()
            ->getResult();
        $statusArchive = $this->statusRepository->findOneBy(['description' => 'Historisée']);

        // Mise a jour le status
        foreach ($oldEvents as $e) {
            $e->setStatus($statusArchive);
        }

        $this->entityManagerInterface->flush();

    }

}
