<?php

namespace App\Controller;

use App\Entity\Classement;
use App\Form\ClassementType;
use App\Repository\ClassementRepository;
use App\Repository\EquipeRepository;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 */
class ClassementController extends AbstractController
{
    /**
     * @Route("/ranks", name="classement_index", methods={"GET"})
     */
    public function index(ClassementRepository $classementRepository, EquipeRepository $equipeRepository, EvenementRepository $evenementRepository): Response
    {
        $classement = new classement();
        $form = $this->createForm(ClassementType::class, $classement);
        return $this->render('dashboard/ranks.html.twig', [
            'classements' => $classementRepository->findAll(),
            'equipes' => $equipeRepository->findAll(),
            'evenements' => $evenementRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/rank/addrank", name="classement_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $classement = new Classement();
        $form = $this->createForm(ClassementType::class, $classement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($classement);
            $entityManager->flush();

            return $this->redirectToRoute('classement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('classement/new.html.twig', [
            'classement' => $classement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/rank/{id}", name="classement_show", methods={"GET"})
     */
    public function show(Classement $classement): Response
    {
        return $this->render('classement/show.html.twig', [
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/rank/{id}/edit", name="classement_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Classement $classement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClassementType::class, $classement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('classement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('classement/edit.html.twig', [
            'classement' => $classement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/rank/{id}/delete", name="classement_delete", methods={"POST"})
     */
    public function delete(Request $request, Classement $classement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$classement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($classement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('classement_index', [], Response::HTTP_SEE_OTHER);
    }
}
