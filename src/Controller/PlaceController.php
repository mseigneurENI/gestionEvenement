<?php

namespace App\Controller;

use App\Entity\Place;
use App\Form\PlaceType;
use App\Repository\EventRepository;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('place', name: 'places_')]
final class PlaceController extends AbstractController
{
    #[Route('', name: 'list')]
    public function list(PlaceRepository $placeRepository): Response
    {
        $places = $placeRepository->findAll();
        return $this->render('place/list.html.twig', [
            'places' => $places,
        ]);
    }


#[Route('/{id}', name: 'show', requirements : ['id' => '\d+'])]
public function show(int $id, EventRepository $eventRepository): Response
{
    $place = $eventRepository->find($id);
    if (!$place) {
        throw $this->createNotFoundException();
    }
    return $this->render('place/show.html.twig', [
        'place' => $place,
    ]);
}

#[Route('/create', name: 'create', methods: ['POST', 'GET'])]
public function Create(EntityManagerInterface $entityManager, Request $request): Response
{
    $place = new Place();

    $placeForm = $this->createForm(PlaceType::class, $place);
    $placeForm->handleRequest($request);

    if ($placeForm->isSubmitted() && $placeForm->isValid()){

        $entityManager->persist($place);
        $entityManager->flush();

        $this->addFlash('success', ' '.$place->getName().' ajouté avec succès !' );
        return $this->redirectToRoute('events_create');
    }

    return $this->render('place/create.html.twig', [
        'placeForm' => $placeForm
    ]);
}

    #[Route('/update/{id}', name: 'update', methods: ['POST', 'GET'])]
    public function update(int $id, PlaceRepository $placeRepository, Request $request, EntityManagerInterface $entityManager)
    {
        $place = $placeRepository->find($id);

        $placeForm = $this->createForm(PlaceType::class, $place);

        $placeForm->handleRequest($request);


    }
}
