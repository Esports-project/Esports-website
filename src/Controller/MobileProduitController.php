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
        $produit = $this->getDoctrine()->getManager()->getRepository(Produit::class)->find($id);
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $normalizer->setIgnoredAttributes(array(
            'user', 'updatedAt', 'ligneCommandes' ,
        ));
        $serializer = new Serializer([$normalizer], [$encoder]);
        $formatted = $serializer->normalize($produit);
        return new JsonResponse($formatted);
    }

    ////////////////////////////////////addd json
    ///

    /******************Ajouter Reclamation*****************************************/
    /**
     * @Route("/addProduitJson", name="addProduitJson")
     * @Method("POST")
     */

    public function ajouterReclamationAction(Request $request)
    {
        $reclamation = new Produit();
        $Prod = new Produit();
        $nom = $request->query->get("nom");
        $price = $request->query->get("price");
       // $quantity = $request->query->get("quantity");
        $referance = $request->query->get("referance");
        $date = new \DateTime('now');

        $em = $this->getDoctrine()->getManager();

        //not default
        $Prod->setNom($nom);
        $Prod->setPrice($price);
        $Prod->setQuantity(1);
        $Prod->setReferance($referance);
        $Prod->setUpdatedAt($date);

        //default

        $Prod->setActive(true);
        $Prod->setDescription("description de produit " );
        $Prod->setImage("test ");
        $Prod->setSolde(null);

        $em->persist($Prod);
        $em->flush();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($Prod);
        return new JsonResponse($formatted);

    }

    /******************Supprimer Produit*****************************************/

    /**
     * @Route("/deleteProduitJson", name="deleteProduitJson")
     * @Method("DELETE")
     */

    public function deletePorduitAction(Request $request) {
        $id = $request->get("id");

        $em = $this->getDoctrine()->getManager();
        $Prod = $em->getRepository(Produit::class)->find($id);
        if($Prod!=null ) {
            $em->remove($Prod);
            $em->flush();

            $serialize = new Serializer([new ObjectNormalizer()]);
            $formatted = $serialize->normalize("Produit a ete supprimee avec success.");
            return new JsonResponse($formatted);

        }
        return new JsonResponse("id produit invalide.");

    }



    /******************Modifier Produit*****************************************/
    /**
     * @Route("/updateProduitJSON", name="updateProduit")
     * @Method("PUT")
     */
    public function modifierProduitActionx(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $Prod = $this->getDoctrine()->getManager()
            ->getRepository(Produit::class)
            ->find($request->get("id"));


        $Prod->setNom($request->get("nom"));
        $Prod->setDescription($request->get("description"));
        $Prod->setQuantity($request->get("quantity"));
        $Prod->setPrice($request->get("price"));

        $em->persist($Prod);
        $em->flush();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($Prod);
        return new JsonResponse("Reclamation a ete modifiee avec success.");

    }

    /******************Detail Reclamation*****************************************/

    /**
     * @Route("/detailProduitJson", name="detailProduitJson")
     * @Method("GET")
     */

    //Detail Reclamation
    public function detailProduit(Request $request)
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
