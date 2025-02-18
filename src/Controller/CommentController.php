<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\Post;
use App\Form\CommentsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/comment')]
class CommentController extends AbstractController
{
    #[Route('/add/{id}', name: 'comment_add', methods: ['POST'])]
    public function add(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comments();
        $form = $this->createForm(CommentsType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPost($post);
            $comment->setCreatedAt(new \DateTime()); 
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire ajouté.');
        }

        return $this->redirectToRoute('post_index', ['id' => $post->getId()]);
    }

    #[Route('/edit/{id}', name: 'comment_edit', methods: ['GET', 'POST'])]
    public function edit(Comments $comment, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentsType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Commentaire mis à jour.');
            return $this->redirectToRoute('post_index', ['id' => $comment->getPost()->getId()]);
        }

        return $this->render('post/show.html.twig', [
            'comment' => $comment,
            'comment_form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'comment_delete', methods: ['POST'])]
    public function delete(Comments $comment, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Commentaire supprimé.');
        }

        return $this->redirectToRoute('post_index', ['id' => $comment->getPost()->getId()]);
    }
}
