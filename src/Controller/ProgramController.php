<?php

namespace App\Controller;

use App\Entity\Episode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Program;
use App\Entity\Season;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProgramType;
use App\Service\Slugify;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use App\Entity\Comment;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Repository\ProgramRepository;
use App\Form\SearchProgramType;

/**
 * @Route("/programs", name="program_")
 */
class ProgramController extends AbstractController
{
    /**
     * The controller for the category add form
     * Display the form or deal with it
     *
     * @Route("/new", name="new")
     */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer): Response
    {
        // Create a new Category Object
        $program = new Program();
        // Create the associated Form
        $form = $this->createForm(ProgramType::class, $program);
        // Get data from HTTP request
        $form->handleRequest($request);
        // Was the form submitted ?
        if ($form->isSubmitted() && $form->isValid()) {
            // Deal with the submitted data
            // Get the Entity Manager
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            $program->setOwner($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            // Persist Category Object
            $entityManager->persist($program);
            // Flush the persisted object
            $entityManager->flush();
            // Finally redirect to categories list
            $email = (new Email())
                ->from('11ff993407-658458@inbox.mailtrap.io')
                ->to('11ff993407-658458@inbox.mailtrap.io')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('program/newProgramEmail.html.twig', ['program' => $program]));

            $mailer->send($email);
            return $this->redirectToRoute('program_index');
        }
        // Render the form
        return $this->render('program/new.html.twig', ["form" => $form->createView()]);
    }

    /**
     * Show all rows from Program’s entity
     *
     * @Route("/", name="index")
     * @return Response A response instance
     */
    public function index(Request $request, ProgramRepository $programRepository): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        $form = $this->createForm(SearchProgramType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData()['search'];
            $programs = $programRepository->findByTitleOrLastname($search);
        } else {
            $programs = $programRepository->findAll();
        }

        return $this->render(
            'program/index.html.twig',
            [
                'programs' => $programs,
                "form" => $form->createView()
            ]
        );
    }

    /**
     * Getting a program by id
     *
     * @Route("/show/{slug}", name="show")
     * @return Response
     */
    public function show(Program $program): Response
    {

        $seasons = $program->getSeasons();

        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : ' . ' found in program\'s table.'
            );
        }
        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
        ]);
    }

    /**
     * Getting a episode by season and program
     *
     * @Route("/{programId}/seasons/{seasonId}", name="season_show")
     * @return Response
     */
    public function showSeason(Program $programId, Season $seasonId)
    {
        $episodes = $seasonId->getEpisodes();


        return $this->render('program/season_show.html.twig', [
            'program' => $programId,
            'season' => $seasonId,
            'episodes' => $episodes,
        ]);
    }

    /**
     * Getting a episode by season and program
     *
     * @Route("/{program}/seasons/{season}/episode/{slug}", name="episode_show")
     * @return Response
     */
    public function showEpisode(Program $program, Season $season, Episode $episode)
    {
        $comments = $this->getDoctrine()
            ->getRepository(Comment::class)
            ->findBy(
                array(),
                array('id' => 'DESC')
            );

        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Program $program): Response
    {
        if (!($this->getUser() == $program->getOwner())) {
            // If not the owner, throws a 403 Access Denied exception
            throw new AccessDeniedException('Only the owner can edit the program!');
        }
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('program_index');
        }

        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }
}
