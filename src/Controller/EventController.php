<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('events', name: 'events_')]
final class EventController extends AbstractController
{
    #[Route('', name: 'list', requirements: ['id' => '\d+'])]
    public function list(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
        return $this->render('event/list.html.twig', [
            'events' => $events,
        ]);

    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);
        if(!$event) {
            throw $this->createNotFoundException();
        }
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);

    }

    #[Route('/create', name: 'create', methods: ['POST', 'GET'])]
    public function create(): Response

    {


    }
    #[Route('/{id}/unsubscribe', name: 'unsubscribe')]
    public function unsubscribe(
        int $id,
        EventRepository $eventRepository,
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

    #[Route('/update', name: 'update', methods: ['POST', 'GET'])]
    public function update(): Response
    {


    }

    #[Route('/{id}/register', name: 'register', methods: ['POST', 'GET'])]
    public function register(
        int $id,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        $event = $eventRepository->find($id);
        $event->addParticipant($user);
        $entityManager->flush();

        $this->addFlash('register', 'Inscription réussie');
        return $this->redirectToRoute('events_list');


    }


    #[Route('/delete', name: 'delete', methods: ['POST', 'GET'])]
    public function delete(
        int $id,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $event = $eventRepository->find($id);
        if(!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $event->remove();
        $entityManager->flush();
        return $this->redirectToRoute('events_list');


    }


}
