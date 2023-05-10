<?php

namespace App\Service;

use App\Entity\Rating;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class RatingService
{
    private $em;
    private $userRepository;
    private $postRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository, PostRepository $postRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
    }

    public function create($userId, $postId, $ratingscore): Rating
    {

        $user = $this->userRepository->find($userId);
        $post = $this->postRepository->find($postId);

        $rating = new Rating();
        $rating->setUser($user);
        $rating->setPost($post);
        $rating->setRating((int)$ratingscore);

        $this->em->persist($rating);
        $this->em->flush();
        return $rating;
    }

    public function getRating($userId, $postId)
    {
        return $this->em->getRepository(Rating::class)
            ->findOneBy(['user' => $userId, 'post' => $postId]);
    }
}
