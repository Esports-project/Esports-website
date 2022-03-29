<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use App\Form\ProduitType;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\HttpFoundation\Request;



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
     * @Route ("/allProd", name="allProduits")
     */
    public function AllProduits(NormalizerInterface $normalizer){
        $em = $this->getDoctrine()->getManager();
        $produit=$em->getRepository(Produit::class)->findAll();
        $jsonContent=$normalizer->normalize($produit,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));

    }
    /**
     * @Route ("/allProduits/{id}", name="allProduits")
     */
    public function ProduitId(Request $request,$id,NormalizerInterface $normalizer){
        $em = $this->getDoctrine()->getManager();
        $produit=$em->getRepository(Produit::class)->find($id);
        $jsonContent=$normalizer->normalize($produit,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }
}
