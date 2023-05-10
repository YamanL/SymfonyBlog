<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Form\ActorType;
use App\Repository\ActorRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/actor")
 */
class ActorController extends AbstractController
{
    /**
     * @var FileUploader
     */
    private $fileUploader;


    public function __construct(FileUploader $fileUploader){

        $this->fileUploader = $fileUploader;
    }

    /**
     * @Route("/", name="actor_index", methods={"GET"})
     */
    public function index(ActorRepository $actorRepository, Request $request): Response
    {
//       dd($request);

        $actors=$actorRepository->getAllActor($request->query->all())->getQuery()->getResult();

        return $this->render('actor/index.html.twig', [

            'actors' => $actors,
        ]);


    }


    /**
     * @Route("/new", name="actor_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {

        $actor = new Actor();
        $form = $this->createForm(ActorType::class, $actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $filename = $this->fileUploader->upload($form->get('image')->getData());

            $actor->setImage($filename);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($actor);
            $entityManager->flush();

            return $this->redirectToRoute('actor_index');
        }

        return $this->render('actor/new.html.twig', [
            'actor' => $actor,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="actor_show", methods={"GET"})
     */
    public function show(actor $actor): Response
    {
        return $this->render('actor/show.html.twig', [
            'actor' => $actor,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="actor_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Actor $actor): Response
    {

        $form = $this->createForm(ActorType::class, $actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $filename = $this->fileUploader->upload($form->get('image')->getData());
            $actor->setImage($filename);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('actor_index');
        }

        return $this->render('actor/edit.html.twig', [
            'actor' => $actor,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/{id}", name="actor_delete", methods={"DELETE"})
     */
    public function delete(Request $request, actor $actor): Response
    {
        if ($this->isCsrfTokenValid('delete'.$actor->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($actor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('actor_index');
    }
}
