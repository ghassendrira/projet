<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\Post;
use App\Form\CommentsType;
use App\Repository\CommentsRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        PostRepository $postRepository,
        CommentsRepository $commentsRepository
    )
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/comment', name: 'app_comment')]
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    #[Route('/post/{id}/comment', name: 'comment_post', methods: ['POST'])]
public function commentPost(int $id, Request $request, EntityManagerInterface $entityManager): Response
{
    $post = $entityManager->getRepository(Post::class)->find($id);
    if (!$post) {
        return $this->json(['error' => 'Post non trouvé'], 404);
    }

    $user = $this->getUser();
    if (!$user) {
        return $this->json(['error' => 'Utilisateur non authentifié'], 401);
    }

    $data = json_decode($request->getContent(), true);
    if (!$data || !isset($data['content']) || empty($data['content'])) {
        return $this->json(['error' => 'Le contenu du commentaire est vide'], 400);
    }

    $comment = new Comments();
    $comment->setContent($data['content']);
    $comment->setPost($post);
    //$comment->setUser($user);
    //$comment->setCreatedAt(new \DateTimeImmutable()); 

    $entityManager->persist($comment);
    $entityManager->flush();

    return $this->json([
        'message' => 'Commentaire ajouté avec succès',
        'comment' => [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
            //'user' => $user->getEmail()
        ]
    ], 201);
}

}
