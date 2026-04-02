<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Event;
use App\Form\CampusType;
use App\Form\EventType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('campus', name: 'campus_', methods: ['GET', 'POST']) ]
#[IsGranted('ROLE_ADMIN')]
final class CampusController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET', 'POST'])]
    public function list(
        Request                     $request,
        EntityManagerInterface      $entityManager
    ): Response {
        $campus = new Campus();
        $campusForm = $this->createForm(CampusType::class, $campus);

        $campusForm->handleRequest($request);

        if ($campusForm->isSubmitted() && $campusForm->isValid()) {
            $entityManager->persist($campus);
            $entityManager->flush();

            $this->addFlash('success', 'Campus ajouté!!!');
            return $this->redirectToRoute('campus_list');
        }

        $listCampus = $entityManager->getRepository(Campus::class)->findAll();

        return $this->render('campus/listCampus.html.twig', [
            'listCampus' => $listCampus,
            'form' => $campusForm->createView(),
        ]);
    }

//    #[IsGranted('EVENT_DELETE', 'event', 'Vous ne pouvez pas supprimer une sortie que vous n\'avez pas créée.')]
    #[Route('/{id}/delete', name: 'delete', methods: ['POST', 'GET'])]
    public function delete(
//        int                    $id,
        Campus                  $campus,
//        EventRepository        $eventRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $entityManager->remove($campus);
        $entityManager->flush();
        $this->addFlash('success', 'Suppression réussie');
        return $this->redirectToRoute('campus_list');
    }

//    #[IsGranted('EVENT_EDIT', 'event', 'Vous ne pouvez pas modifier une sortie que vous n\'avez pas créée.')]
    #[Route('/{id}/update', name: 'update', methods: ['GET', 'POST'])]
    public function update(
//        int $id,
        Campus                  $campus,
//        EventRepository $eventRepository,
        EntityManagerInterface $entityManagerInterface,
        Request                $request
    ): Response
    {
//        $event = $eventRepository->find($id);

        $campusForm = $this->createForm(CampusType::class, $campus);
        $campusForm->handleRequest($request);

        if ($campusForm->isSubmitted() && $campusForm->isValid()) {
            $entityManagerInterface->persist($campus);
            $entityManagerInterface->flush();

            $this->addFlash('success', 'Modification réussie');

            return $this->redirectToRoute('campus_list');
        }

        $listCampus = $entityManagerInterface->getRepository(Campus::class)->findAll();

        return $this->render('campus/listCampus.html.twig', [
            'listCampus' => $listCampus,
            'form' => $campusForm->createView(),
        ]);
    }

}
