<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use App\Form\ProduitType;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


class MobileProduitController extends AbstractController
{
    /**
     * @Route("/mobile/produit", name="app_mobile_produit")
     */
    public function index(): Response
    {
        return $this->render('mobile_produit/index.html.twig', [
            'controller_name' => 'MobileProduitController',
        ]);
    }
    /**
     * @Route ("/allProduits", name="allProduits")
     */
    public function AllProduits(NormalizerInterface $normalizer){
        $repository=$this->getDoctrine()->getRepository(Produit::class);
        $produits=$repository->findAll();
        $jsonContent=$normalizer->normalize($produits, 'json',['groups'=>'post::read']);
        return new Response(json_encode($jsonContent));
    }
}
