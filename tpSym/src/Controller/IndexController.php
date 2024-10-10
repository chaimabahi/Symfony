<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request; // Add this import
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class IndexController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'article_list')]
    public function home(): Response
    {
        // Retrieve all articles from the database
        $articles = $this->entityManager->getRepository(Article::class)->findAll();

        return $this->render('articles/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/article/save', name: 'article_save')]
    public function save(): Response
    {
        // Use the injected entity manager
        $entityManager = $this->entityManager;

        // Add three articles
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

        // Flush to save the articles in the database
        $entityManager->flush();

        return new Response('Articles enregistrés avec ids: ' . $article1->getId() . ', ' . $article2->getId() . ', ' . $article3->getId());
    }

 /**
     * @Route("/article/new", name="new_article", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article); // Use ArticleType form class
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $this->entityManager->persist($article);
            $this->entityManager->flush();

            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

/**
     * @Route("/article/edit/{id}", name="edit_article", methods={"GET", "POST"})
     */
    public function edit(Request $request, $id): Response
    {
        // Find the article by its ID
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        // If the article is not found, throw an exception
        if (!$article) {
            throw $this->createNotFoundException(
                'No article found for id ' . $id
            );
        }

        // Create the form using ArticleType
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        // If form is submitted and valid, flush the changes to the database
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            // Redirect to the article list after saving the changes
            return $this->redirectToRoute('article_list');
        }

        // Render the edit form view
        return $this->render('articles/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

#[Route('/article/{id}', name: 'article_show')]
    public function show($id): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        return $this->render('articles/show.html.twig', ['article' => $article]);
    }
    #[Route('/article/delete/{id}', name: 'delete_article', methods: ['DELETE'])]
    public function delete(Request $request, $id): Response
    {
        // Fetch the article to be deleted
        $article = $this->entityManager->getRepository(Article::class)->find($id);
        
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        // Remove the article
        $entityManager = $this->entityManager;
        $entityManager->remove($article);
        $entityManager->flush();

        // Redirect after deletion
        return $this->redirectToRoute('article_list');
    }



}