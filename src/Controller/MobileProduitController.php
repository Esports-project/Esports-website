<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use App\Form\ProduitType;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;




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
     * @Route ("/AllProduits", name="AllProduits")
     */
    public function AllProduits(NormalizerInterface $normalizer){
        $em = $this->getDoctrine()->getManager();
        $produit=$em->getRepository(Produit::class)->findAll();
        $jsonContent=$normalizer->normalize($produit,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route ("/AllProduits/{id}", name="AllProduitsId")
     */
    public function ProduitId(Request $request,$id,NormalizerInterface $normalizer){
        $em = $this->getDoctrine()->getManager();
        $produit=$em->getRepository(Produit::class)->find($id);
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($produit);
        return new JsonResponse($formatted);
    }

    /**
     * @Route("/detailProd", name="detail_reclamation")
     * @Method("GET")
     */
    public function detailProduitAction(Request $request)
    {
        $id = $request->get("id");

        $em = $this->getDoctrine()->getManager();
        $reclamation = $this->getDoctrine()->getManager()->getRepository(Produit::class)->find($id);
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getDescription();
        });
        $serializer = new Serializer([$normalizer], [$encoder]);
        $formatted = $serializer->normalize($reclamation);
        return new JsonResponse($formatted);
    }

}
