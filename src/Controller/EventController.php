<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Status;
use App\Form\EventType;
use App\Form\FiltreEventType;
use App\Repository\CampusRepository;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\Clock\now;

#[Route('events', name: 'events_')]
final class EventController extends AbstractController
{
    #[Route('', name: 'list')]
    public function list(Request $request, EventRepository $eventRepository, CampusRepository $campusRepository): Response
    {
$filtreForm = $this->createForm(FiltreEventType::class);
$filtreForm->handleRequest($request);
$events = $eventRepository->findPublishedEventByDate();
   if($filtreForm->isSubmitted() && $filtreForm->isValid()) {
       $data = $filtreForm->getData();
       $campus = $data['campus'] ;
       $search = $data['search'];
       $beginDate = $data['beginDate'];
       $endDate = $data['endDate'];
       $checkboxes = $data['checkbox'];

       $events = $eventRepository->findFilteredEvents($campus, $search, $beginDate, $endDate, $checkboxes);
   }
    return $this->render('event/list.html.twig', ['events' => $events, 'filtreForm' => $filtreForm]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);
        if (!$event) {
            throw $this->createNotFoundException();
        }
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);

    }

    #[Route('/{id}/register', name: 'register', methods: ['POST', 'GET'])]
    public function register(
        int                    $id,
        EventRepository        $eventRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $this->getUser();

        $event = $eventRepository->find($id);
        $event->addParticipant($user);
        $entityManager->flush();

        $this->addFlash('register', 'Inscription réussie');
        return $this->redirectToRoute('events_list');


    }

    #[Route('/{id}/unsubscribe', name: 'unsubscribe')]
    public function unsubscribe(
        int                    $id,
        EventRepository        $eventRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $this->getUser();
        $event = $eventRepository->find($id);

        $event->removeParticipant($user);

        $entityManager->flush();
        $this->addFlash('cancel', 'Annulation réussie');
        return $this->redirectToRoute('events_list');
    }


    #[Route('/create', name: 'create', methods: ['POST', 'GET'])]
    public function createOrUpdate(
        EntityManagerInterface $entityManager,
        EventRepository        $eventRepository,
        StatusRepository       $statusRepository,
        Request                $request,
        int                    $id = null
    ): Response
    {
        $event = new Event();
        if ($id != null) {
            $event = $eventRepository->find($id);
            if ($event->getOrganiser() != $this->getUser()) {
                throw $this->createAccessDeniedException("Vous ne pouvez pas modifier une sortie que vous n'avez pas crée.");
            }
        }

        $eventForm = $this->createForm(EventType::class, $event);
        $eventForm->handleRequest($request);

        if ($eventForm->isSubmitted() && $eventForm->isValid()) {
            $event->setOrganiser($this->getUser());


            //on attribue automatiquement le statut En création à la sortie
            if ($event->getStatus() === null) {
                $statusEnCreation = $statusRepository->findOneBy(['description' => 'En création']);
            }
            if (!$statusEnCreation) {
                throw $this->createNotFoundException('Le status « en création » n\existe pas en base de données');
            }

            $event->setStatus($statusEnCreation);
            $event->addParticipant($this->getUser());

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Votre sortie a bien été créée !');

            return $this->redirectToRoute('events_list');
        }

        return $this->render($id ? 'event/update.html.twig' : 'event/create.html.twig', ['eventForm' => $eventForm]);
    }


    #[Route('/delete', name: 'delete', methods: ['POST', 'GET'])]
    public function delete(
        int                    $id,
        EventRepository        $eventRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $event = $eventRepository->find($id);
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $event->remove();
        $entityManager->flush();
        return $this->redirectToRoute('events_list');


    }
    #[Route('/{id}/update', name: 'update', methods: ['GET','POST'])]
    public function update(
        int $id,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response
    {
        $event = $eventRepository->find($id);

        $eventform = $this->createForm(EventType::class, $event);
        $eventform->handleRequest($request);

        if ($eventform->isSubmitted() && $eventform->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Modification réussie');

            return $this->redirectToRoute('events_show', ['id' => $event->getId()]);
        }

        return $this->render('event/update.html.twig', [
            'eventform' => $eventform,
        ]);
    }
}
