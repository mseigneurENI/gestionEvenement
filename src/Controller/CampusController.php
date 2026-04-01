<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('campus', name: 'campus_')]
#[IsGranted('ROLE_ADMIN')]
final class CampusController extends AbstractController
{
    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function add(
        Request                     $request,
        EntityManagerInterface      $entityManagerInterface
    ): Response
    {
        $campus = new Campus();

        $campusForm = $this->createForm(CampusType::class, $campus);
        $campusForm->handleRequest($request);

        if ($campusForm->isSubmitted() && $campusForm->isValid()) {

            $entityManagerInterface->persist($campus);
            $entityManagerInterface->flush();

            $this->addFlash('success', 'Campus ajouté (:');
            return $this->redirectToRoute('campus_create');
        }

        return $this->render('campus/create.html.twig', [
            'form' => $campusForm->createView(),
        ]);
    }

}
