<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ForumstatsController extends AbstractController
{
    #[Route('/forumstats', name: 'app_forumstats')]
    public function index(): Response
    {
        return $this->render('forumstats/index.html.twig', [
            'controller_name' => 'ForumstatsController',
        ]);
    }
}
