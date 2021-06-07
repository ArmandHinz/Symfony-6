<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Comment;
use App\Entity\Episode;
use App\Form\CommentFormType;



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
}
