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


use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/")
 */
class ReclamationController extends AbstractController
{
    /**
     * @Route("/dashboard/reclamations", name="reclamation_index", methods={"GET"})
     */
    public function index(ReclamationRepository $reclamationRepository, CategoriesRepository $categoriesRepository): Response
    {
        return $this->render('dashboard/reclamations.html.twig', [
            'categories' => $categoriesRepository->findAll(),
            'reclamations' => $reclamationRepository->findAll(),
        ]);
    }
    
    /**
     * @Route("/reclamation/new", name="reclamation_new", methods={"GET", "POST"})
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
     * @Route("/reclamation/{id}/edit", name="reclamation_edit", methods={"GET", "POST"})
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
     * @Route("/reclamation/{id}", name="reclamation_delete", methods={"POST"})
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
    public function reply(Request $request, UserRepository $userRepository ,Reclamation $reclamation ,EntityManagerInterface $entityManager, \Swift_Mailer $mailer): Response
    {
        $faq = new Faq();
        $faq->setCategory($reclamation->getCategory());
        $form = $this->createForm(FaqType::class, $faq);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation->setStatus(1);
            $entityManager->persist($faq);
            $entityManager->flush();
            

            $message = (new \Swift_Message('Reclamation treated'))
            ->setFrom('runtimeerrorlevelup@gmail.com')
            ->setTo($reclamation->getEmail())
            ->setBody(
                $this->renderView(
                // templates/emails/registration.html.twig
                    'reclamation/mail.html.twig', [
                    'faq' => $faq
                ]),
                'text/html'
            )
            // you can remove the following code if you don't define a text version for your emails
            ->addPart(
                $this->renderView('reclamation/mail.html.twig', [
                    'faq' => $faq
                ]),
                'text/plain'
            );

        $mailer->send($message);
            

            return $this->redirectToRoute('reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/reply.html.twig', [
            'reclamation' => $reclamation,
            'categorys' => $reclamation->getCategory(),
            'users' => $userRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("rc/showAllJson", name="reclamation_indexjson", methods={"GET"})
     */
    public function showAll(ReclamationRepository $reclamationRepository, NormalizerInterface $normalizer): Response
    {
       $reclamation = $reclamationRepository->findAll();

       $encoder = new JsonEncoder();
       $normalizer = new ObjectNormalizer();
       $normalizer->setCircularReferenceLimit(0); 
       $normalizer->setCircularReferenceHandler(function ($object) {
           return $object->getId();
       });
             $normalizer->setIgnoredAttributes(array(
             'user', 'date', 
        ));

       $serializer = new Serializer([$normalizer],[$encoder]);
       $formatted = $serializer->normalize($reclamation);

       return new JsonResponse($formatted);
    }

    /**
     * @Route("rc/editJson/{id}", name="updateReclamationJSON", methods={"GET"})
     */
    public function editJson(Request $request,ReclamationRepository $reclamationRepository, NormalizerInterface $normalizer, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $reclamation = $em->getRepository(Reclamation::class)->find($id);
        if($request->get('status') != null)
        $reclamation->setStatus($request->get('status'));
        if($request->get('description') != null)
        $reclamation->setDescription($request->get('description'));
        $em->flush();
       
        
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceLimit(0); 
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
              $normalizer->setIgnoredAttributes(array(
              'user', 'date', 
         ));
 
        $serializer = new Serializer([$normalizer],[$encoder]);
        $formatted = $serializer->normalize($reclamation);
        
        return new Response("Reclamation updated successfully".json_encode($formatted));
    }


      /**
     * @Route("rc/removeJson/{id}", name="removeReclamationJSON", methods={"GET"})
     */
    public function removeJson(Request $request,ReclamationRepository $reclamationRepository, NormalizerInterface $normalizer, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $reclamation = $em->getRepository(Reclamation::class)->find($id);
        $em->remove($reclamation);
        $em->flush();
        $jsonContent = $normalizer->normalize($reclamation, 'json',['groups'=>'post:read']);

        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceLimit(0); 
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
              $normalizer->setIgnoredAttributes(array(
              'user', 'date', 
         ));
 
        $serializer = new Serializer([$normalizer],[$encoder]);
        $formatted = $serializer->normalize($reclamation);

        return new Response("Reclamation deleted successfully".json_encode($formatted));
    }

     /**
     * @Route("rc/addJson/", name="addReclamationJSON", methods={"GET"})
     */
    public function addJson(Request $request,ReclamationRepository $reclamationRepository, NormalizerInterface $normalizer)
    {
        $em = $this->getDoctrine()->getManager();
        $reclamation = new Reclamation();
        $reclamation->setDate(new \Datetime('now'));
        $reclamation->setStatus(0);
        $reclamation->setSujet($request->get('sujet'));
        $reclamation->setDescription($request->get('description'));
        $reclamation->setEmail($request->get('email'));
        $em->persist($reclamation);
        $em->flush();
       
        
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceLimit(0); 
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
              $normalizer->setIgnoredAttributes(array(
              'user', 'date', 
         ));
 
        $serializer = new Serializer([$normalizer],[$encoder]);
        $formatted = $serializer->normalize($reclamation);
        
        return new Response("Reclamation added successfully".json_encode($formatted));
    }
    
  
  
}   
