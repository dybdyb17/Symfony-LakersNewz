<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleFormType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/articles')]
#[IsGranted('ROLE_ADMIN')]
class AdminArticleController extends AbstractController
{
    #[Route('', name: 'admin_articles_index')]
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/articles/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'admin_articles_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'Article créé avec succès');
            return $this->redirectToRoute('admin_articles_index');
        }

        return $this->render('admin/articles/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_articles_edit', methods: ['GET', 'POST'])]
    public function edit(Article $article, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUpdatedAt(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'Article modifié avec succès');
            return $this->redirectToRoute('admin_articles_index');
        }

        return $this->render('admin/articles/edit.html.twig', [
            'form' => $form,
            'article' => $article,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_articles_delete', methods: ['POST'])]
    public function delete(Article $article, EntityManagerInterface $em): Response
    {
        $em->remove($article);
        $em->flush();

        $this->addFlash('success', 'Article supprimé');
        return $this->redirectToRoute('admin_articles_index');
    }
}
