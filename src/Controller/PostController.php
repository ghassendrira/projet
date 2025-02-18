<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\Like;
use App\Entity\Post;
use App\Form\CommentsType;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{

    private $entityManager;
    private $postRepository;

    public function __construct(EntityManagerInterface $entityManager, PostRepository $postRepository)
    {
        $this->entityManager = $entityManager;
        $this->postRepository = $postRepository;
    }

    #[Route('/posts', name: 'post_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();
    
        // Créer un formulaire pour chaque post et ne stocker que la vue
        $commentForms = [];
        foreach ($posts as $post) {
            $comment = new Comments();
            $form = $this->createForm(CommentsType::class, $comment);
            $commentForms[$post->getId()] = $form->createView(); // ✅ Correction ici
        }
    
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'commentForms' => $commentForms, // ✅ Passer les vues des formulaires
        ]);
    }
    

    #[Route('/post/new', name: 'post_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $post->setContent($request->request->get('content'));
        $post->setCreatedAt(new \DateTime()); 
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
    
    
    
            $bannedWords = ['mot1', 'mot2', 'mot3'];
    
            /** @var UploadedFile $imageFile */
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $imageFile = $form->get('image')->getData();
    
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename. '-'. uniqid(). '.'. $imageFile->guessExtension();
                $extension = strtolower($imageFile->guessExtension()); 
    
                if (!in_array($extension, $allowedExtensions)) {
                    $this->addFlash('danger', 'Format d\'image non autorisé.');
                    return $this->redirectToRoute('post_new');
                }
    
                $bannedKeywords = ['nsfw', '18+', 'porn', 'xxx'];
    
                try {
                    $imageFile->move(
                        $this->getParameter('post_images_directory'),
                        $newFilename
                    );
                    $post->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Échec du téléchargement de l\'image.');
                    return $this->redirectToRoute('post_new'); 
                }
            }
    
            $entityManager->persist($post);
            $entityManager->flush();
    
            return $this->redirectToRoute('post_index');
        }
    
        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/post/{id}', name: 'post_show', methods: ['GET', 'POST'])]
    public function show(Post $post, Request $request, EntityManagerInterface $entityManager , PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();

        $comment = new Comments();

        $form = $this->createForm(CommentsType::class, $comment);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPost($post);
            $entityManager->persist($comment);
            $entityManager->flush();
    
            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès.');
    
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }
    
        return $this->render('post/show.html.twig', [
            'post' => $posts,
            'comment_form' => $form->createView(), 
        ]);
    }
    
    
    #[Route('/post/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
public function edit(int $id, Request $request, PostRepository $postRepository, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $post = $postRepository->find($id);

    if (!$post) {
        throw $this->createNotFoundException('Le post demandé n\'existe pas.');
    }

    $oldImage = $post->getImage();
    $form = $this->createForm(PostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('post_images_directory'),
                    $newFilename
                );
                $post->setImage($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Échec du téléchargement de l\'image.');
            }
        } else {
            $post->setImage($oldImage);
        }

        $entityManager->flush();
        return $this->redirectToRoute('app_post_index');
    }

    return $this->render('post/edit.html.twig', [
        'form' => $form->createView(),
        'post' => $post,
    ]);
}
    #[Route('/post/{id}/delete', name: 'post_delete', methods: ['POST'])]
public function delete(int $id, Request $request , PostRepository $postRepository, EntityManagerInterface $entityManager): Response
{
    $post = $postRepository->find($id);

    if (!$post) {
        throw $this->createNotFoundException('Post not found');
    }
    if (!$this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
        throw $this->createAccessDeniedException('Invalid CSRF token');
    }

    $entityManager->remove($post);
    $entityManager->flush();

    return $this->redirectToRoute('app_post_index');
}



#[Route('/post/{id}/comment', name: 'post_comment', methods: ['POST'])]
public function comment(Post $post, Request $request, EntityManagerInterface $entityManager): Response
{
    // Créer un nouveau commentaire
    $comment = new Comments();
    $form = $this->createForm(CommentsType::class, $comment);
    $form->handleRequest($request);

    // Vérifier si le formulaire est valide
    if ($form->isSubmitted() && $form->isValid()) {
        $comment->setPost($post);
        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Commentaire ajouté avec succès.');

        return $this->redirectToRoute('post_index', ['id' => $post->getId()]);
    }

    return $this->redirectToRoute('post_index', ['id' => $post->getId()]);
}



#[Route('/post/{id}/like', name: 'post_like', methods: ['POST'])]
public function like(int $id, PostRepository $postRepository, EntityManagerInterface $entityManager, Request $request): Response
{
    $post = $postRepository->find($id);
    if (!$post) {
        throw $this->createNotFoundException('Post non trouvé.');
    }

    $post->like();
    $entityManager->persist($post); 
    $entityManager->flush();

    return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_post_index'));
}

}
