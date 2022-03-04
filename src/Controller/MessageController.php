<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/message")
 */
class MessageController extends AbstractController
{
    /**
     * @Route("/messages", name="app_message_dashboard", methods={"GET"})
     */
    public function dashboard(MessageRepository $messageRepository): Response
    {
        return $this->render('dashboard/messages.html.twig', [
            'messages' => $messageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/", name="app_message_index", methods={"GET"})
     */
    public function index(MessageRepository $messageRepository): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        return $this->render('message/index.html.twig', [
            'messages' => $messageRepository->findAll(),
            'form' => $form->createview(),
        ]);
    }

    /**
     * @Route("/new", name="app_message_new", methods={"GET", "POST"})
     */
    public function new(Request $request, MessageRepository $messageRepository): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);
        $message->setDate(new \DateTime('now'));
        $message->setSender($this->getUser());
        $message->setSeen(0);

        if ($form->isSubmitted() && $form->isValid()) {
            $messageRepository->add($message);
            return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('message/index.html.twig', [
            'messages' => $messageRepository->findAll(),
            'form' => $form->createview(),
        ]);
    }

    /**
     * @Route("/reply/{receiver}", name="app_message_reply", methods={"GET", "POST"})
     */
    public function reply(Request $request, int $receiver, MessageRepository $messageRepository): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);
        $message->setDate(new \DateTime('now'));
        $message->setSender($this->getUser());
        $message->setSeen(0);

        if ($form->isSubmitted() && $form->isValid()) {
            $messageRepository->add($message);
            return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('message/reply.html.twig', [
            'messages' => $messageRepository->findMessages($receiver),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_message_show", methods={"GET"})
     */
    public function show(Message $message): Response
    {
        return $this->render('message/show.html.twig', [
            'message' => $message,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_message_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Message $message, MessageRepository $messageRepository): Response
    {
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $messageRepository->add($message);
            return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('message/edit.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_message_delete", methods={"POST"})
     */
    public function delete(Request $request, Message $message, MessageRepository $messageRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $messageRepository->remove($message);
        }

        return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
    }
}
