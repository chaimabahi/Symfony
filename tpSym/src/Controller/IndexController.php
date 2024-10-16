<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\PropertySearch;
use App\Form\PropertySearchType;
use App\Form\CategorySearchType;
use App\Entity\CategorySearch;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'article_list')]
    public function home(Request $request): Response
    {
        $propertySearch = new PropertySearch();
        $form = $this->createForm(PropertySearchType::class, $propertySearch);
        $form->handleRequest($request);

        // Initializing articles array
        $articles = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $nom = $propertySearch->getNom();
            if ($nom) {
                // Search for articles by name
                $articles = $this->entityManager
                    ->getRepository(Article::class)
                    ->findBy(['nom' => $nom]);
            } else {
                // Show all articles if no search term provided
                $articles = $this->entityManager
                    ->getRepository(Article::class)
                    ->findAll();
            }
        }

        return $this->render('articles/index.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }

    #[Route('/article/save', name: 'article_save')]
    public function save(): Response
    {
        $entityManager = $this->entityManager;

        // Adding three articles
        $article1 = new Article();
        $article1->setNom('Article 1');
        $article1->setPrix(1000.00);
        $entityManager->persist($article1);

        $article2 = new Article();
        $article2->setNom('Article 2');
        $article2->setPrix(2000.50);
        $entityManager->persist($article2);

        $article3 = new Article();
        $article3->setNom('Article 3');
        $article3->setPrix(3000.99);
        $entityManager->persist($article3);

        $entityManager->flush();

        return new Response('Articles enregistrés avec ids: ' . $article1->getId() . ', ' . $article2->getId() . ', ' . $article3->getId());
    }

    #[Route('/article/new', name: 'new_article', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($article);
            $this->entityManager->flush();

            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/edit/{id}', name: 'edit_article', methods: ['GET', 'POST'])]

    public function edit(Request $request, int $id): Response
    {
        // Find the article by ID
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('No article found for id ' . $id);
        }

        // Create the form using ArticleType
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/{id}', name: 'article_show')]
    public function show(int $id): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        return $this->render('articles/show.html.twig', ['article' => $article]);
    }

    #[Route('/article/delete/{id}', name: 'delete_article', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        // Fetch the article to be deleted
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        // CSRF token validation
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            // Remove the article
            $this->entityManager->remove($article);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('article_list');
    }

    #[Route('/category/newCat', name: 'new_category', methods: ['GET', 'POST'])]
    public function newCategory(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/newCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/art_cat/', name: 'article_par_cat', methods: ['GET', 'POST'])]
    public function articlesParCategorie(Request $request): Response
    {
        $categorySearch = new CategorySearch();
        $form = $this->createForm(CategorySearchType::class, $categorySearch);
        $form->handleRequest($request);
    
        $articles = [];
    
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $categorySearch->getCategory();
    
            if ($category) {
                $articles = $category->getArticles();
            } else {
                $articles = $this->entityManager->getRepository(Article::class)->findAll();
            }
        }
    
        return $this->render('articles/articlesParCategorie.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }

}    