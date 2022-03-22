<?php

namespace App\Controller;

use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\Json;

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
     * @Route("/displayProduits", name="display_produits")
     */
    public function allProdAction(NormalizerInterface $Normalizer)
    {
        $repository = $this->getDoctrine()->getRepository(Produit::class);
        $produit = $repository->findAll();
        $jsonContent = $Normalizer->normalize($produit, 'json',['groups'=>'post:read']);
        $serializer = new Serializer([new ObjectNormalizer()]);
        return new Response(json_encode($jsonContent));
    }

}
