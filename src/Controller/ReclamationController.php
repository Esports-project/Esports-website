<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Faq;
use App\Form\FaqType;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use App\Repository\CategoriesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/reclamation")
 */
class ReclamationController extends AbstractController
{
    /**
     * @Route("/", name="reclamation_index", methods={"GET"})
     */
    public function index(ReclamationRepository $reclamationRepository, CategoriesRepository $categoriesRepository): Response
    {
        return $this->render('dashboard/reclamations.html.twig', [
            'categories' => $categoriesRepository->findAll(),
            'reclamations' => $reclamationRepository->findAll(),
        ]);
    }

    /**
     * @Route("/showAllJson", name="reclamation_indexjson", methods={"GET"})
     */
    public function showAll(ReclamationRepository $reclamationRepository, NormalizerInterface $normalizer): Response
    {
        $jsonContent = $normalizer->normalize($reclamationRepository->findAll(), 'json', ['groups'=> 'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route("/new", name="reclamation_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager, ReclamationRepository $reclamationRepository): Response
    {

        if($reclamationRepository->checkForSpam($this->getUser()) >= 3){
            return $this->render('base-error.html.twig', [ ]);
        }else{
            $reclamation = new Reclamation();
            $reclamation->setDate(new \Datetime('now'));
            $reclamation->setStatus(0);
            $form = $this->createForm(ReclamationType::class, $reclamation);
    
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $session = $request->getSession();
                $session->set('rec_sujet', $reclamation->getSujet());
                $session->set('rec_description', $reclamation->getDescription());
                $session->set('rec_user', $reclamation->getUser());
                $session->set('rec_email', $reclamation->getEmail());
                $session->set('rec_date', $reclamation->getDate());
                $session->set('rec_status', $reclamation->getStatus());
                $session->set('rec_category', $reclamation->getCategory());   
          
                return $this->redirectToRoute('app_faq_index', ['id' => $reclamation->getCategory()->getId()], Response::HTTP_SEE_OTHER);
            }   

            return $this->render('reclamation/new.html.twig', [
                'reclamation' => $reclamation,
                'form' => $form->createView(),
            ]);
        }
       
    }

    /**
     * @Route("/{id}", name="reclamation_show", methods={"GET"})
     */
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="reclamation_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, UserRepository $userRepository ,Reclamation $reclamation, CategoriesRepository $categoriesRepository ,EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'categorys' => $categoriesRepository->findAll(),
            'users' => $userRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="reclamation_delete", methods={"POST"})
     */
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('reclamation_index', [], Response::HTTP_SEE_OTHER);
    }

     /**
     * @Route("/{id}/reply", name="reclamation_reply", methods={"GET", "POST"})
     */
    public function reply(Request $request, UserRepository $userRepository ,Reclamation $reclamation ,EntityManagerInterface $entityManager): Response
    {
        $faq = new Faq();
        $faq->setCategory($reclamation->getCategory());
        $form = $this->createForm(FaqType::class, $faq);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($faq);
            $entityManager->flush();
            return $this->redirectToRoute('reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/reply.html.twig', [
            'reclamation' => $reclamation,
            'categorys' => $reclamation->getCategory(),
            'users' => $userRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }
  
}   
