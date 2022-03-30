<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MobileCommandeController extends AbstractController
{
    /**
     * @Route("/mobile/commande", name="app_mobile_commande")
     */
    public function index(): Response
    {
        return $this->render('mobile_commande/index.html.twig', [
            'controller_name' => 'MobileCommandeController',
        ]);
    }
}
