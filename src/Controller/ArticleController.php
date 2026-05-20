<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function home(ArticleRepository $articleRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 6;
        $totalArticles = $articleRepository->count([]);
        $totalPages = ceil($totalArticles / $limit);

        $articles = $articleRepository->findBy([], ['createdAt' => 'DESC'], $limit, ($page - 1) * $limit);

        return $this->render('pages/accueil.html.twig', [
            'articles' => $articles,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/article/{id}', name: 'app_article_show')]
    public function articleShow(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/recherche', name: 'app_recherche')]
    public function recherche(Request $request, ArticleRepository $articleRepository): Response
    {
        $terme = $request->query->get('q', '');
        $articles = [];

        if ($terme !== '') {
            $articles = $articleRepository->rechercher($terme);
        }

        return $this->render('article/recherche.html.twig', [
            'articles' => $articles,
            'terme' => $terme,
        ]);
    }
}
