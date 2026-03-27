<?php

namespace App\Controller;


use App\Entity\Event;
use App\Form\CancellationFormType;
use App\Form\EventType;
use App\Form\FiltreEventType;
use App\Repository\CampusRepository;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('events', name: 'events_')]
final class EventController extends AbstractController
{
    private StatusRepository $statusRepository;

    public function __construct(StatusRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }

    #[Route('', name: 'list')]
    public function list(Request $request, EventRepository $eventRepository): Response
    {
        $filtreForm = $this->createForm(FiltreEventType::class);
        $filtreForm->handleRequest($request);

        $events = $eventRepository->findPublishedEventByDate();

        if ($filtreForm->isSubmitted() && $filtreForm->isValid()) {
            $data = $filtreForm->getData();
            $campus = $data['campus'];
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
            'event' => $event
        ]);

    }

    #[Route('/myEvents', name: 'my_Events')]
    public function showMine(
        EventRepository $eventRepository
    )
    {
        $user = $this->getUser();
        $myEvents = $eventRepository->findMyEvents($user);

        return $this->render('event/myEvents.html.twig', ['myEvents' => $myEvents]);
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
        if ($event->getStatus()->getDescription() !== 'Ouverte') {
            throw $this->createAccessDeniedException('Vous ne pouvez pas vous inscrire à cette sortie');
        }
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
    public function create(
        EntityManagerInterface $entityManager,
//        EventRepository        $eventRepository,
        StatusRepository       $statusRepository,
        Request                $request,
        int                    $id = null
    ): Response
    {
        $event = new Event();
//        if ($id != null) {
//            $event = $eventRepository->find($id);
//            if ($event->getOrganiser() != $this->getUser()) {
//                throw $this->createAccessDeniedException("Vous ne pouvez pas modifier une sortie que vous n'avez pas créée.");
//            }
//        }

        $user = $this->getUser();
        if ($user && $user->getCampus()) { //on vérifie que l'utilisateur existe, donc qu'il est connecté ET qu'il a un campus
            $event->setCampus($user->getCampus());
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
                throw $this->createNotFoundException('Le status « en création » n\'existe pas en base de données');
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

    #[IsGranted('EVENT_DELETE', 'event', 'Vous ne pouvez pas supprimer une sortie que vous n\'avez pas créée.')]
    #[Route('/{id}/delete', name: 'delete', methods: ['POST', 'GET'])]
    public function delete(
//        int                    $id,
        Event                  $event,
//        EventRepository        $eventRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
//        $event = $eventRepository->find($id);
//        if (!$event) {
//            throw $this->createNotFoundException('Event not found');
//        }

        $entityManager->remove($event);
        $entityManager->flush();
        $this->addFlash('succes', 'Supperession réussie');
        return $this->redirectToRoute('events_list');
    }

    #[IsGranted('EVENT_EDIT', 'event', 'Vous ne pouvez pas modifier une sortie que vous n\'avez pas créée.')]
    #[Route('/{id}/update', name: 'update', methods: ['GET', 'POST'])]
    public function update(
//        int $id,
        Event                  $event,
//        EventRepository $eventRepository,
        EntityManagerInterface $entityManager,
        Request                $request
    ): Response
    {
//        $event = $eventRepository->find($id);

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

    #[Route('/{id}/publish', name: 'publish', methods: ['POST'])]
    public function publish(int $id, EventRepository $eventRepository, EntityManagerInterface $entityManagerinterface): Response
    {
        $event = $eventRepository->find($id);

        if ($event->getOrganiser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $status = $this->statusRepository->findOneBy(['description' => 'Ouverte']);


        $event->setStatus($status);
        $entityManagerinterface->flush();

        $this->addFlash('success', 'La sortie est publiée');
        return $this->redirectToRoute('events_show', ['id' => $id]);
    }


    #[Route('/{id}/reasonCancel', name: 'reasonCancel', methods: ['GET', 'POST'])]
    public function cancelReason(
        int                    $id,
        EventRepository        $eventRepository,
        Request                $request,
        EntityManagerInterface $entityManagerinterface
    ): Response
    {
        ////TODO corriger les paramètres de la fonction pour remplacer int $id par Event $event
        $event = $eventRepository->find($id);
        if (!$event) {
            throw $this->createNotFoundException();
        }

        if ($event->getOrganiser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(CancellationFormType::class);
        $event = $eventRepository->find($id);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reason = $form->get('reason')->getData();


            $cancelledStatus = $this->statusRepository->findOneBy(['description' => 'Annulée']);
            $event->setStatus($cancelledStatus);
            $event->setCancellationReason($reason);
            $entityManagerinterface->flush();

            $this->addFlash('success', 'Sortie annulée avec motif : ' . $reason);
            return $this->redirectToRoute('events_show', ['id' => $id]);
        }

        return $this->render('event/reasonCancel.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }
}
