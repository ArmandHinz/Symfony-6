<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Comment;
use App\Entity\Episode;
use App\Form\CommentFormType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;




/**
 * @Route("/comment", name="comment_")
 */
class CommentController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    /**
     * @Route("/{slug}", name="new")
     */

    public function new(Request $request, Episode $episode)
    {
        // Create a new Category Object
        $comment = new Comment();
        $user = $this->getUser();
        // Create the associated Form
        $form = $this->createForm(CommentFormType::class, $comment);
        // Get data from HTTP request
        $form->handleRequest($request);
        // Was the form submitted ?
        if ($form->isSubmitted()) {
            // Deal with the submitted data
            // Get the Entity Manage
            $entityManager = $this->getDoctrine()->getManager();
            // Persist COmment Object
            $comment->setAuthor($user);
            $comment->setEpisode($episode);

            $entityManager->persist($comment);
            // Flush the persisted object
            $entityManager->flush();
            // Finally redirect 


            return $this->redirectToRoute('program_index');
        }
        // Render the form
        return $this->render('comment/new.html.twig', ["form" => $form->createView()]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Comment $comment): Response
    {
        if (!($this->getUser() == $comment->getAuthor())) {
            // If not the owner, throws a 403 Access Denied exception
            throw new AccessDeniedException('Only the owner can edit the comment!');
        }
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('program_index');
        }

        return $this->render('program/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"POST"})
     */
    public function delete(Request $request, Comment $comment): Response
    {
        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('program_index');
    }
}
