<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Status;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        if (!$event) {
            throw $this->createNotFoundException();
        }
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);

    }

    #[Route('/create', name: 'create', methods: ['POST', 'GET'])]
    #[Route('/update/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['POST', 'GET'])]
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
            if ($event->getStatus() === null){
                $statusEnCreation = $statusRepository->findOneBy(['description' => 'En création']);
            }
            if (!$statusEnCreation){
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
    public function delete(): Response
    {

    }


}
