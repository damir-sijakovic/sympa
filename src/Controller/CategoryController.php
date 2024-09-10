<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Article;
use App\Entity\ArticleCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

use App\Helper\UtilityHelper;

class CategoryController extends AbstractController
{    
    private $entityManager;
    private $utilityHelper;
    private $slugger;
    
	public function __construct(EntityManagerInterface $entityManager, 
        UtilityHelper $utilityHelper,
        SluggerInterface $slugger)
    {
        $this->entityManager = $entityManager;	
        $this->utilityHelper = $utilityHelper;	
        $this->slugger = $slugger;
	}        
     
   
    public function create(Request $request): Response
    {
        $name = $request->request->get('name');
        $type = $request->request->get('type');
        $parent = $request->request->get('parent');
        $description = $request->request->get('description');
        $image = $request->request->get('image');
        $visible = $request->request->get('visible');

        // Check if the required fields are provided
        if (!$name || !$type) {
            return new Response('Name and Type are required', Response::HTTP_BAD_REQUEST);
        }

        // Create new Category entity
        $category = new Category();
        $category->setName($name);
        $category->setType($type);
        $category->setParent($parent ? (int)$parent : null);
        $category->setDescription($description);
        $category->setImage($image);
        $category->setVisible($visible ? (bool)$visible : false);

        // Generate slug using the slugger
        $slug = $this->slugger->slug($name)->lower();
        $category->setSlug($slug);

        // Persist and flush to save the new Category
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return new Response('Category created successfully');
    }
   
    public function delete(int $id): Response
    {
        $category = $this->entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            return new Response('Category not found', 404);
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return new Response('Category deleted successfully');
    }   
   
   
    public function addArticle(Request $request): Response
    {
        $categoryId = $request->request->get('categoryId');
        $articleId = $request->request->get('articleId');

        $category = $this->entityManager->getRepository(Category::class)->find($categoryId);
        $article = $this->entityManager->getRepository(Article::class)->find($articleId);

        if (!$category || !$article) {
            return new Response('Category or Article not found', Response::HTTP_NOT_FOUND);
        }

        $articleCategory = new ArticleCategory();
        $articleCategory->setCategory($category);
        $articleCategory->setArticle($article);

        $this->entityManager->persist($articleCategory);
        $this->entityManager->flush();

        return new Response('Article added to Category successfully');
    }


    public function deleteArticle(Request $request): Response
    {
        $categoryId = $request->request->get('categoryId');
        $articleId = $request->request->get('articleId');

        $articleCategory = $this->entityManager->getRepository(ArticleCategory::class)
            ->findOneBy(['category' => $categoryId, 'article' => $articleId]);

        if (!$articleCategory) {
            return new Response('Article not linked to the Category or not found', Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($articleCategory);
        $this->entityManager->flush();

        return new Response('Article removed from Category successfully');
    }
    
};