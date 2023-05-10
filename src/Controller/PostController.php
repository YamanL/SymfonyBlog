<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Rating;
use App\Form\CommentType;
use App\Form\PostType;
use App\Form\ReplyType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\ActorRepository;
use App\Repository\UserRepository;
use App\Service\RatingService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

use Knp\Component\Pager\PaginatorInterface;
use phpDocumentor\Reflection\Types\Nullable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{

    /**
     * @Route("/post", name="post")
     */
    public function index(PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {

        $posts = $paginator->paginate(
            $postRepository->getAllPost($request->query->all()), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('post/index.html.twig', [
           'posts' => $posts
        ]);
    }


    /**
     * @Route("/post/new", name="post_new")
     */
    public function new(Request $request){
        if (!$this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $post->setCreatedAt(new DateTime());
            $post->setAuthor($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Your post was added'
            );
            return $this->redirectToRoute('post');
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/{id}", name="post_show")
     * @param Request $request
     * @param Post $post
     * @param PostRepository $postRepository
     * @param EntityManagerInterface $em
     * @param RatingService $ratingService
     * @param CommentRepository $commentRepository
     * @return Response
     */

    public function show(Request $request, Post $post, PostRepository $postRepository, EntityManagerInterface $em, RatingService $ratingService, CommentRepository $commentRepository): Response
    {
       $ratingService->getRating($this->getUser()->getId(), $post->getId());

        // create the form for creating new comments
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // check if this is a reply to an existing comment
            $parentId = $request->get('parent_id');
            if ($parentId) {
                $parentComment = $commentRepository->find($parentId);
                if ($parentComment) {
                    $comment->setParent($parentComment);
                }
            }

            $postId = $request->attributes->get('id');
            $post = $postRepository->find($postId);

            $commentForm = $this->createForm(CommentType::class, $comment);
            $commentForm->handleRequest($request);
            $this->addComment($commentForm, $comment, $post);

            // set the rest of the comment data and save it to the database
            $comment->setAuthor($this->getUser());
            $comment->setPost($post);
            $comment->setCreatedAt(new DateTime());
            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Comment added successfully!');

            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }


        $reply = new Comment();
    $replyForm = $this->createForm(ReplyType::class, $reply);
    $replyForm->handleRequest($request);

    if ($replyForm->isSubmitted() && $replyForm->isValid()) {
        $parentId = $request->get('parent_id');
        $parentComment = $commentRepository->find($parentId);
        $reply->setPost($post);
        $reply->setParent($parentComment);
        $em->persist($reply);
        $em->flush();

        $this->addFlash('success', 'Your reply has been added.');

        return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
    }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'ratingService' => $ratingService,
            'commentForm' => $form->createView(),
            'rating' => 'Average Rating:'

        ]);
    }

    /**
     * @Route("/post/{id}/edit", name="post_edit")
     */
    public function edit(Post $post, Request $request)
    {
        if ($this->getUser() !== $post->getAuthor()) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Your post was edited'
            );
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('/post/edit.html.twig', [
            'post' => $post,
            'editForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/ratings", name="rating_create", methods={"POST","GET"})
     */
    public function create(Request $request, RatingService $ratingService): JsonResponse
    {
//        $data = json_decode($request->getContent(), true);

//        $rating = $ratingService->create($data['user_id'], $data['post_id'], $data['rating']);
        $rating = $ratingService->create($request->get("user"),$request->get("post"),$request->get("rating"));

        return new JsonResponse(['message' => 'Rating created','rating'=>$rating->getRating()]);
    }




//    // Add comment
//    private function addComment($replyForm, $comment, $post): void
//    {
//        if ($replyForm->isSubmitted() && $replyForm->isValid()) {
//            $parentId = $request->get('parent_id');
//            $parentComment = $commentRepository->find($parentId);
//            $reply->setPost($post);
//            $reply->setParent($parentComment);
//            $em->persist($reply);
//            $em->flush();
//
//            $this->addFlash('success', 'Your reply has been added.');
//
//            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
//        }
//
//        return $this->render('post/show.html.twig', [
//            'post' => $post,
//            'replyForm' => $replyForm->createView(),
//        ]);
//    }
    private function addComment(\Symfony\Component\Form\FormInterface $commentForm, Comment $comment, ?Post $post)
    {
    }


}
