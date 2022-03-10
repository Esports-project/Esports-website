<?php

namespace App\Controller;

use App\Entity\LigneCommande;
use App\Entity\Produit;
use App\Services\Cart\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Form\CommandeType;
use Symfony\Component\HttpFoundation\Request;



class CommandeController extends AbstractController
{
    /**
     * @Route("/commande", name="commande")
     */
    public function index(): Response
    {
        return $this->render('commande/index.html.twig', [
            'controller_name' => 'CommandeController',
        ]);
    }

    /**
     * @Route("/dashboard/AfficheCommande", name="AfficheCommande")
     */
    public function AfficheCommande(){
        $repository=$this->getDoctrine()->getRepository(Commande::class);
        $commande=$repository->findAll();
        return $this->render('commande/AfficheC.html.twig',
            ['commande'=>$commande]);
    }

    /**
     * @Route("/SupprimerCommande/{id}", name="SupprimerCommande")
     */
    public function SupprimerCommande($id, CommandeRepository $repository, CartService $cartService){
        $commande=$repository->find($id);
        $em=$this->getDoctrine()->getManager();

        $prodsArr = $cartService->getFullCart();
        for ($i=1; $i<$commande->getQuantite(); $i++){
            $prodsArr[$i]["product"]->setQuantity($prodsArr[$i]["product"]->getQuantity() + 1);
        }


        $em->remove($commande);
        $em->flush();
        return $this->redirectToRoute('AfficheCommande');
    }

    /**
     * @Route("/AjouterCommande", name="AjouterCommande")
     */
    public function AjouterCommande(Request $request, CartService $cartService){
        $commande=new Commande();
        $em=$this->getDoctrine()->getManager();
        $commande->setDate(new \DateTime());
        $commande->setPrixTotal($cartService->getTotal());
        $commande->setQuantite($cartService->getQuantity());
        $commande->setUser($this->getUser());
        $prodsArr = $cartService->getFullCart();
        for ($i=0; $i<$commande->getQuantite(); $i++){
            $nouvLigne = new LigneCommande();
            $nouvLigne->setProduit($prodsArr[$i]["product"]);
            $nouvLigne->setQuantite($prodsArr[$i]["quantity"]);
            $nouvLigne->setCommande($commande);
            $em->persist($nouvLigne);
            $prodsArr[$i]["product"]->setQuantity($prodsArr[$i]["product"]->getQuantity() - 1);
        }
        
        
        $em->persist($commande);
        $em->flush();
        $prodsArr = $cartService->emptyCart();
        return $this->redirectToRoute('AfficheCommande');

    }


    /**
     * @Route("/ModifierCommande/{id}", name="ModifierCommande")
     */
    function ModifierCommande(CommandeRepository $repository, $id, Request $request){
        $commande=$repository->find($id);
        $form=$this->createForm(CommandeType::class,$commande);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('AfficheCommande');
        }
        return $this->render('commande/ModifierC.html.twig',
            ['form'=>$form->createView()]);
    }

}