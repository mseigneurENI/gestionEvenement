<?php

namespace App\Repository;

use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findMyEvents(User $user): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('status,organiser,campus,place,city,participants')
            ->leftJoin('e.status', 'status')
            ->leftJoin('e.organiser', 'organiser')
            ->leftJoin('e.participants', 'participants')
            ->leftJoin('e.campus', 'campus')
            ->leftJoin('e.place', 'place')
            ->leftJoin('place.city', 'city')
            ->andWhere('e.organiser = :user')
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    public function findPublishedEventByDate(): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('e,status,organiser,campus,place,city,participants')
            ->leftJoin('e.status', 'status')
            ->leftJoin('e.organiser', 'organiser')
            ->leftJoin('e.participants', 'participants')
            ->leftJoin('e.campus', 'campus')
            ->leftJoin('e.place', 'place')
            ->leftJoin('place.city', 'city')
            ->andwhere('status.description NOT IN (:forbiddenStatus)')
            ->setParameter('forbiddenStatus', ["En création", "Historisée"])
            ->addOrderBy('e.beginDateEvent', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function findFilteredEvents(?Campus $campus = null, ?string $search = '', ?\DateTimeInterface $beginDate = null, ?\DateTimeInterface $endDate = null, array $checkboxes = [], $user = null, $id = null): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('e,status,organiser,campus,place,city,participants')
            ->leftJoin('e.status', 'status')
            ->leftJoin('e.organiser', 'organiser')
            ->leftJoin('e.participants', 'participants')
            ->leftJoin('e.campus', 'campus')
            ->leftJoin('e.place', 'place')
            ->leftJoin('place.city', 'city')
            ->andwhere('status.description NOT IN (:forbiddenStatus)')
            ->setParameter('forbiddenStatus', ["En création", "Historisée"]);
        if ($campus) {
            $qb->andWhere('e.campus = :campus')
                ->setParameter('campus', $campus);
        }
        if ($search) {
            $qb->andWhere('e.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if ($beginDate) {
            $qb->andWhere('e.beginDateEvent >= :beginDate')
                ->setParameter('beginDate', $beginDate);
        }
        if ($endDate) {
            $qb->andWhere('e.beginDateEvent <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        if ($checkboxes) {
            if(in_array('organisateur', $checkboxes)) {
                $qb->andWhere('e.organiser = :user')
                    ->setParameter('user', $user);
            }

            if (in_array('enregistre', $checkboxes)) {
                $qb->leftJoin('e.participants', 'p')
                    ->andWhere('p.id = :id' )
                    ->setParameter('id', $id);
            }

            if (in_array('libre', $checkboxes)) {
                $qb->andWhere(':id NOT MEMBER OF e.participants')
                    ->setParameter('id', $id);
            }

            if (in_array('terminee', $checkboxes)) {
                $qb->andWhere('e.status in (:statutTerminee)')
                    ->setParameter('statutTerminee', "Terminée");
            }
        }

        $qb->orderBy('e.beginDateEvent', 'ASC');
        return $qb->getQuery()->getResult();
    }


    /**
     * @return Event[] Returns an array of Event objects
     */
    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findOneBySomeField($value): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findOneEventById(int $id): ?Event{
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('e,status,organiser,campus,place,city,participants')
            ->leftJoin('e.status', 'status')
            ->leftJoin('e.organiser', 'organiser')
            ->leftJoin('e.participants', 'participants')
            ->leftJoin('e.campus', 'campus')
            ->leftJoin('e.place', 'place')
            ->leftJoin('place.city', 'city')
            ->andWhere('e.id = :id')
            ->setParameter('id', $id);
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllMyCompleteEventstoChange(): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('e,status,organiser,campus,place,city,participants')
            ->leftJoin('e.status', 'status')
            ->leftJoin('e.organiser', 'organiser')
            ->leftJoin('e.participants', 'participants')
            ->leftJoin('e.campus', 'campus')
            ->leftJoin('e.place', 'place')
            ->leftJoin('place.city', 'city')
            ->andwhere('status.description NOT IN (:forbiddenStatus)')
            ->setParameter('forbiddenStatus', ["En création", "Supprimée", "Annulée", "Historisée"])
            ->addOrderBy('e.beginDateEvent', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function findAllEventstoHistorize(): array{
        $limitDate = new \DateTimeImmutable('-1 month');
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('e,status,organiser,campus,place,city,participants')
            ->leftJoin('e.status', 'status')
            ->leftJoin('e.organiser', 'organiser')
            ->leftJoin('e.participants', 'participants')
            ->leftJoin('e.campus', 'campus')
            ->leftJoin('e.place', 'place')
            ->leftJoin('place.city', 'city')
            ->andwhere('status.description = :terminated')
            ->setParameter('terminated', 'Terminée')
            ->andWhere('e.endDate < :limitDate')
            ->setParameter('limitDate', $limitDate)
            ->addOrderBy('e.beginDateEvent', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function findAllEventstoClose():array{
        $limitDate = new \DateTimeImmutable('now');
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('e,status,organiser,campus,place,city,participants')
            ->leftJoin('e.status', 'status')
            ->leftJoin('e.organiser', 'organiser')
            ->leftJoin('e.participants', 'participants')
            ->leftJoin('e.campus', 'campus')
            ->leftJoin('e.place', 'place')
            ->leftJoin('place.city', 'city')
            ->andwhere('status.description = :open')
            ->setParameter('open', 'Ouvert')
            ->andWhere('e.limitDateRegistration < :limitDate')
            ->setParameter('limitDate', $limitDate)
            ->addOrderBy('e.beginDateEvent', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function findAllEventstoCloseMaxParticipants():array{
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('e,status,organiser,campus,place,city,participants')
            ->leftJoin('e.status', 'status')
            ->leftJoin('e.organiser', 'organiser')
            ->leftJoin('e.participants', 'participants')
            ->leftJoin('e.campus', 'campus')
            ->leftJoin('e.place', 'place')
            ->leftJoin('place.city', 'city')
            ->andwhere('status.description = :open')
            ->setParameter('open', 'Ouvert')
//            ->andwhere('e.registrationMaxNb <= e.participants.length')
            // ->andWhere('e.limitDateRegistration > :limitDate')
            // ->setParameter('limitDate', $limitDate)
            ->addOrderBy('e.beginDateEvent', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function findAllEventstoOpenAfterMaxParticipants(): array{
        $limitDate = new \DateTimeImmutable('now');
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('e,status,organiser,campus,place,city,participants')
            ->leftJoin('e.status', 'status')
            ->leftJoin('e.organiser', 'organiser')
            ->leftJoin('e.participants', 'participants')
            ->leftJoin('e.campus', 'campus')
            ->leftJoin('e.place', 'place')
            ->leftJoin('place.city', 'city')
            ->andwhere('status.description = :closed')
            ->setParameter('closed', 'Clôturée')
            ->andWhere('e.limitDateRegistration > :limitDate')
            ->setParameter('limitDate', $limitDate)
//            ->andwhere('e.registrationMaxNb > e.participants.length')
            ->addOrderBy('e.beginDateEvent', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function findAllEventstoFinish(): array{
        $limitDate = new \DateTimeImmutable('now');
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('e,status,organiser,campus,place,city,participants')
            ->leftJoin('e.status', 'status')
            ->leftJoin('e.organiser', 'organiser')
            ->leftJoin('e.participants', 'participants')
            ->leftJoin('e.campus', 'campus')
            ->leftJoin('e.place', 'place')
            ->leftJoin('place.city', 'city')
            ->andwhere('status.description = :inProgress')
            ->setParameter('inProgress', 'En cours')
            ->andWhere('e.endDate < :limitDate')
            ->setParameter('limitDate', $limitDate)
            ->addOrderBy('e.beginDateEvent', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function findAllEventsInProgress():array{
        $limitDate = new \DateTimeImmutable('now');
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('e,status,organiser,campus,place,city,participants')
            ->leftJoin('e.status', 'status')
            ->leftJoin('e.organiser', 'organiser')
            ->leftJoin('e.participants', 'participants')
            ->leftJoin('e.campus', 'campus')
            ->leftJoin('e.place', 'place')
            ->leftJoin('place.city', 'city')
            ->andwhere('status.description IN (:authorizedStatus)')
            ->setParameter('authorizedStatus', ['Ouverte', 'Clôturée'])
            ->andWhere('e.beginDateEvent < :limitDate')
            ->andWhere('e.endDate > :limitDate')
            ->setParameter('limitDate', $limitDate)
            ->addOrderBy('e.beginDateEvent', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function findAllEventsByStatusToChange(): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('e,status,organiser,campus,place,city,participants')
            ->leftJoin('e.status', 'status')
            ->leftJoin('e.organiser', 'organiser')
            ->leftJoin('e.participants', 'participants')
            ->leftJoin('e.campus', 'campus')
            ->leftJoin('e.place', 'place')
            ->leftJoin('place.city', 'city')
            ->andwhere('status.description NOT IN (:forbiddenStatus)')
            ->setParameter('forbiddenStatus', ["En création", "Annulée", "Historisée"])
            ->addOrderBy('e.beginDateEvent', 'ASC');
        return $qb->getQuery()->getResult();
    }

}
