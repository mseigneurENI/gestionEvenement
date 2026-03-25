<?php

namespace App\Controller;

use App\Repository\EventRepository;
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

    #[Route('/update', name: 'update', methods: ['POST', 'GET'])]
    public function update(): Response
    {

    }


    #[Route('/delete', name: 'delete', methods: ['POST', 'GET'])]
    public function delete(): Response
    {

    }


}
