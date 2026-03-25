<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('', name: 'main_home')]
    public function home(): Response
    {
        return $this->render('events/list.html.twig');
    }

    #[Route('/cgu', name: 'main_cgu')]
public function cgu(): Response{
        return $this->render('main/cgu.html.twig');
    }

    #[Route('/mentionsLegales', name: 'main_mentionsLegales')]
public function mentionsLegales(): Response{
        return $this->render('main/mentionsLegales.html.twig');
    }
}
