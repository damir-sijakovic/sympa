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
        $parent = $request->request->get('parent');
        $description = $request->request->get('description');
        $image = $request->request->get('image', '');
        $visible = $request->request->get('visible');
        $type = $request->request->get('type', 'article');

        // Check if the required fields are provided
        if (!$name) {
            return new Response('Name is required', Response::HTTP_BAD_REQUEST);
        }

        // Create new Category entity
        $category = new Category();
        $category->setName($name);
        $category->setType($type);
        $category->setParent($parent ? (int)$parent : null);
        $category->setDescription($description);
        $category->setImage($image);
        $category->setVisible($visible ? (bool)$visible : false);
        $category->setCreatedAt(new \DateTimeImmutable());
             
        // Generate slug using the slugger
        $slug = $this->slugger->slug($name)->lower();
        $category->setSlug($slug);

        // Persist and flush to save the new Category
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $this->json([
            'error' => false,
            'message' => 'Category created successfully',
            'data' => null,
        ]);
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
    
    
    public function get(Request $request): Response
    { 
        try {
            $perPage = $request->query->getInt('per-page', 0); 
            $page = $request->query->getInt('page', 1);
            $sort = $request->query->get('sort', 'desc'); 
            $search = $request->query->get('search'); 
            $type = $request->query->get('type', 'article'); 
            $offset = ($page - 1) * $perPage;

            if ($sort === 'id-asc') {
                $order = ['id' => 'ASC'];
            } elseif ($sort === 'id-desc') {
                $order = ['id' => 'DESC'];
            } else {
                $order = ($sort === 'asc') ? ['name' => 'ASC'] : ['name' => 'DESC'];
            }

            $categoryRepository = $this->entityManager->getRepository(Category::class);

            $queryBuilder = $categoryRepository->createQueryBuilder('t');

            if (!empty($search)) {
                $queryBuilder->andWhere('t.name LIKE :search OR t.slug LIKE :search')
                             ->setParameter('search', '%' . $search . '%');
            }

            $queryBuilder->andWhere('t.type = :type')
                         ->setParameter('type', $type);

            $totalQuery = clone $queryBuilder;
            $totalCategories = count($totalQuery->getQuery()->getResult());

            if ($perPage > 0) {
                $queryBuilder->orderBy('t.' . key($order), current($order))
                             ->setFirstResult($offset)
                             ->setMaxResults($perPage);
            } else {
                $queryBuilder->orderBy('t.' . key($order), current($order));
            }

            $categories = $queryBuilder->getQuery()->getResult();

            $categoryData = [];
            foreach ($categories as $category) {
                $categoryData[] = [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'slug' => $category->getSlug(),
                    'type' => $category->getType(),
                    'parent' => $category->getParent(),
                    'description' => $category->getDescription(),
                    'image' => $category->getImage(),
                    'visible' => $category->getVisible(),
                    'created_at' => $category->getCreatedAt()->format('Y-m-d H:i:s'),
                    'modified_at' => $category->getModifiedAt() ? $category->getModifiedAt()->format('Y-m-d H:i:s') : null,            
                ];
            }

            $paginateData = ($perPage > 0)
                ? $this->utilityHelper->paginate($totalCategories, $perPage, $page, 3)
                : null; 

            return $this->json(['total' => $totalCategories, 'categories' => $categoryData, 'paginateData' => $paginateData]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function getOld(Request $request): Response
    {
        try {
            $perPage = $request->query->getInt('per-page', 10);
            $page = $request->query->getInt('page', 1);
            $sort = $request->query->get('sort', 'desc'); // Default sort order is descending
            $active = $request->query->get('active'); // Get the active parameter
            $search = $request->query->get('search'); // Get the search parameter
            $offset = ($page - 1) * $perPage;

            // Determine the sorting order based on the "sort" parameter
            if ($sort === 'id-asc') {
                $order = ['id' => 'ASC'];
            } elseif ($sort === 'id-desc') {
                $order = ['id' => 'DESC'];
            } else {
                // Default to sorting by createdAt
                $order = ($sort === 'asc') ? ['createdAt' => 'ASC'] : ['createdAt' => 'DESC'];
            }

            $categoryRepository = $this->entityManager->getRepository(Category::class);

            $queryBuilder = $categoryRepository->createQueryBuilder('c');

            if ($active === '1') {
                $queryBuilder->andWhere('c.visible = :visible')->setParameter('visible', true);
            } elseif ($active === '0') {
                $queryBuilder->andWhere('c.visible = :visible')->setParameter('visible', false);
            }


            if (!empty($search)) {
                $queryBuilder->andWhere('c.name LIKE :search OR c.description LIKE :search')
                             ->setParameter('search', '%' . $search . '%');
            }

            $totalQuery = clone $queryBuilder;
            $totalCategories = count($totalQuery->getQuery()->getResult());

            $queryBuilder->orderBy('c.' . key($order), current($order))
                         ->setFirstResult($offset)
                         ->setMaxResults($perPage);

            $categories = $queryBuilder->getQuery()->getResult();

            $categoryData = [];
            foreach ($categories as $category) {
                $categoryData[] = [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'slug' => $category->getSlug(),
                    'type' => $category->getType(),
                    'parent' => $category->getParent(),
                    'description' => $category->getDescription(),
                    'image' => $category->getImage(),
                    'visible' => $category->getVisible(),
                    'created_at' => $category->getCreatedAt()->format('Y-m-d H:i:s'),
                    'modified_at' => $category->getModifiedAt() ? $category->getModifiedAt()->format('Y-m-d H:i:s') : null,
                ];
            }

            $paginateData = $this->utilityHelper->paginate($totalCategories, $perPage, $page, 3); // 3 = visible pages

            return $this->json(['total' => $totalCategories, 'categories' => $categoryData, 'paginateData' => $paginateData]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getProducts(Request $request): Response
    {
        try {
            $perPage = $request->query->getInt('per-page', 10);
            $page = $request->query->getInt('page', 1);
            $sort = $request->query->get('sort', 'desc'); // Default sort order is descending
            $active = $request->query->get('active'); // Get the active parameter
            $search = $request->query->get('search'); // Get the search parameter
            $offset = ($page - 1) * $perPage;

            // Determine the sorting order based on the "sort" parameter
            if ($sort === 'id-asc') {
                $order = ['id' => 'ASC'];
            } elseif ($sort === 'id-desc') {
                $order = ['id' => 'DESC'];
            } else {
                // Default to sorting by createdAt
                $order = ($sort === 'asc') ? ['createdAt' => 'ASC'] : ['createdAt' => 'DESC'];
            }

            $categoryRepository = $this->entityManager->getRepository(Category::class);

            //ADDED ?
            $queryBuilder = $categoryRepository->createQueryBuilder('a')
                ->where('a.type = :type')
                ->setParameter('type', 'product');


            $queryBuilder = $categoryRepository->createQueryBuilder('c');

            if ($active === '1') {
                $queryBuilder->andWhere('c.visible = :visible')->setParameter('visible', true);
            } elseif ($active === '0') {
                $queryBuilder->andWhere('c.visible = :visible')->setParameter('visible', false);
            }


            if (!empty($search)) {
                $queryBuilder->andWhere('c.name LIKE :search OR c.description LIKE :search')
                             ->setParameter('search', '%' . $search . '%');
            }

            $totalQuery = clone $queryBuilder;
            $totalCategories = count($totalQuery->getQuery()->getResult());

            $queryBuilder->orderBy('c.' . key($order), current($order))
                         ->setFirstResult($offset)
                         ->setMaxResults($perPage);

            $categories = $queryBuilder->getQuery()->getResult();

            $categoryData = [];
            foreach ($categories as $category) {
                $categoryData[] = [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'slug' => $category->getSlug(),
                    'type' => $category->getType(),
                    'parent' => $category->getParent(),
                    'description' => $category->getDescription(),
                    'image' => $category->getImage(),
                    'visible' => $category->getVisible(),
                    'created_at' => $category->getCreatedAt()->format('Y-m-d H:i:s'),
                    'modified_at' => $category->getModifiedAt() ? $category->getModifiedAt()->format('Y-m-d H:i:s') : null,
                ];
            }

            $paginateData = $this->utilityHelper->paginate($totalCategories, $perPage, $page, 3); // 3 = visible pages

            return $this->json(['total' => $totalCategories, 'categories' => $categoryData, 'paginateData' => $paginateData]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function edit(Request $request): Response
    {
        $id = $request->request->get('id');
        $name = $request->request->get('name');
        $parent = $request->request->get('parent');
        $description = $request->request->get('description');
        $active = $request->request->get('active');
        $image = $request->request->get('image');

        if (!$id) 
        {
            return $this->json([ 'error' => true, 'data' => null, 'message' => "Catgeory 'id' is required." ], Response::HTTP_BAD_REQUEST);
        }

        if (!$name) 
        {
            return $this->json([ 'error' => true, 'data' => null, 'message' => "Catgeory 'name' is required." ], Response::HTTP_BAD_REQUEST);
        }

        if (!$description) 
        {
            return $this->json([ 'error' => true, 'data' => null, 'message' => "Catgeory 'description' is required." ], Response::HTTP_BAD_REQUEST);
        }

        if (!$active) 
        {
            return $this->json([ 'error' => true, 'data' => null, 'message' => "Catgeory 'active' is required." ], Response::HTTP_BAD_REQUEST);
        }

        if (!$image) 
        {
            return $this->json([ 'error' => true, 'data' => null, 'message' => "Catgeory 'image' is required." ], Response::HTTP_BAD_REQUEST);
        }

        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $category = $id ? $categoryRepository->find($id) : new Category();

        if (!$category) {
            return $this->json([
                'error' => true,
                'data' => null,
                'message' => 'Category not found',            
            ], Response::HTTP_NOT_FOUND);
        }

        $slug = strtolower($this->slugger->slug($name)->toString());
        $category->setName($name);        
        $category->setSlug($slug);        
        
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $this->json([
                'error' => false,
                'data' => null,
                'message' => 'Category saved successfully',            
            ]);
    }
      
       
    public function editProduct(Request $request): Response
    {
        $id = $request->request->get('id');
        $name = $request->request->get('name');
        $parent = $request->request->get('parent');
        $description = $request->request->get('description');
        $active = $request->request->get('active');
        $image = $request->request->get('image');

        if (!$id) 
        {
            return $this->json([ 'error' => true, 'data' => null, 'message' => "Catgeory 'id' is required." ], Response::HTTP_BAD_REQUEST);
        }

        if (!$name) 
        {
            return $this->json([ 'error' => true, 'data' => null, 'message' => "Catgeory 'name' is required." ], Response::HTTP_BAD_REQUEST);
        }

        if (!$description) 
        {
            return $this->json([ 'error' => true, 'data' => null, 'message' => "Catgeory 'description' is required." ], Response::HTTP_BAD_REQUEST);
        }

        if (!$active) 
        {
            return $this->json([ 'error' => true, 'data' => null, 'message' => "Catgeory 'active' is required." ], Response::HTTP_BAD_REQUEST);
        }
/*
        if (!$image) 
        {
            return $this->json([ 'error' => true, 'data' => null, 'message' => "Catgeory 'image' is required." ], Response::HTTP_BAD_REQUEST);
        }
*/
        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $category = $id ? $categoryRepository->find($id) : new Category();

        if (!$category) {
            return $this->json([
                'error' => true,
                'data' => null,
                'message' => 'Category not found',            
            ], Response::HTTP_NOT_FOUND);
        }

        $slug = strtolower($this->slugger->slug($name)->toString());
        $category->setName($name);        
        $category->setSlug($slug);        
        
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $this->json([
                'error' => false,
                'data' => null,
                'message' => 'Category saved successfully',            
            ]);
    }
       
          
       
       
    public function getCategoriesByIds(Request $request): Response
    {
        try {
            // Get the "ids" string from the request, e.g., "11,1,34,5"
            $idsString = $request->request->get('ids');

            $ids = array_map('intval', explode(',', $idsString));

            if (empty($ids)) {
                return $this->json(['error' => 'No IDs provided.'], Response::HTTP_BAD_REQUEST);
            }

            $categoryRepository = $this->entityManager->getRepository(Category::class);

            // Fetch the categories with the provided IDs
            $queryBuilder = $categoryRepository->createQueryBuilder('c')
                                               ->where('c.id IN (:ids)')
                                               ->setParameter('ids', $ids);

            $categories = $queryBuilder->getQuery()->getResult();

            $categoryData = [];
            foreach ($categories as $category) {
                $categoryData[] = [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'slug' => $category->getSlug(),
                    'type' => $category->getType(),
                    'parent' => $category->getParent(),
                    'description' => $category->getDescription(),
                    'image' => $category->getImage(),
                    'visible' => $category->getVisible(),
                    'created_at' => $category->getCreatedAt()->format('Y-m-d H:i:s'),
                    'modified_at' => $category->getModifiedAt() ? $category->getModifiedAt()->format('Y-m-d H:i:s') : null,
                ];
            }

            return $this->json(['categories' => $categoryData]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    } 
    
    
    public function addArticleToCategories(Request $request): Response
    {
        $articleId = $request->request->get('article_id');
        $categoryIds = $request->request->get('category_ids');

        if (!$articleId) {
            return $this->json([
                'error' => true,
                'message' => 'Article ID not provided',
                'data' => null,
            ], 400);
        }

        if (!$categoryIds){
            $articleCategories = $this->entityManager->getRepository(ArticleCategory::class)->findBy(['article' => $articleId]);        
            foreach ($articleCategories as $articleCategory) {
                $this->entityManager->remove($articleCategory);
            }
            $this->entityManager->flush();
            return $this->json([
                'error' => false,
                'message' => 'Article linked to categories successfully',
                'data' => null,
            ], 200);            
        }

        $categoryIdsArray = array_map('trim', explode(',', $categoryIds));
        $article = $this->entityManager->getRepository(Article::class)->find($articleId);

        if (!$article) {
            return $this->json([
                'error' => true,
                'message' => 'Article not found',
                'data' => null,
            ], 404);
        }
        
        //delete old links
        $articleCategories = $this->entityManager->getRepository(ArticleCategory::class)->findBy(['article' => $articleId]);        
        foreach ($articleCategories as $articleCategory) {
            $this->entityManager->remove($articleCategory);
        }
        
        foreach ($categoryIdsArray as $categoryId) {
            if (!is_numeric($categoryId)) {
                return $this->json([
                    'error' => true,
                    'message' => 'Invalid category ID provided',
                    'data' => null,
                ], 400);
            }

            $category = $this->entityManager->getRepository(Category::class)->find($categoryId);

            if (!$category) {
                return $this->json([
                    'error' => true,
                    'message' => 'Category with ID ' . $categoryId . ' not found',
                    'data' => null,
                ], 404);
            }

            $articleCategory = new ArticleCategory();
            $articleCategory->setArticle($article);
            $articleCategory->setCategory($category);
            $articleCategory->setType('article');
            $this->entityManager->persist($articleCategory);            
        }

        $this->entityManager->flush();

        return $this->json([
            'error' => false,
            'message' => 'Article linked to categories successfully',
            'data' => null,
        ], 200);
    }

    
  
    public function linkArticlesToCategory(Request $request): Response
    {
        $articleIds = $request->request->get('article_ids');
        $categoryId = $request->request->get('category_id');

        if (!$articleIds || !$categoryId) {
            return $this->json([
                'error' => true,
                'message' => 'Article IDs or Category ID not provided',
                'data' => null,
            ], 400);
        }

        $articleIdsArray = array_map('trim', explode(',', $articleIds));

        $category = $this->entityManager->getRepository(Category::class)->find($categoryId);

        if (!$category) {
            return $this->json([
                'error' => true,
                'message' => 'Category not found',
                'data' => null,
            ], 404);
        }

        foreach ($articleIdsArray as $articleId) {
            if (!is_numeric($articleId)) {
                return $this->json([
                    'error' => true,
                    'message' => 'Invalid article ID provided: ' . $articleId,
                    'data' => null,
                ], 400);
            }

            $article = $this->entityManager->getRepository(Article::class)->find($articleId);

            if (!$article) {
                return $this->json([
                    'error' => true,
                    'message' => 'Article with ID ' . $articleId . ' not found',
                    'data' => null,
                ], 404);
            }

            $existingArticleCategory = $this->entityManager->getRepository(ArticleCategory::class)
                ->findOneBy(['article' => $article, 'category' => $category]);

            if (!$existingArticleCategory) {
                $articleCategory = new ArticleCategory();
                $articleCategory->setArticle($article);
                $articleCategory->setCategory($category);
                $articleCategory->setType('article');
                $this->entityManager->persist($articleCategory);
            }
        }

        $this->entityManager->flush();

        return $this->json([
            'error' => false,
            'message' => 'Articles linked to category successfully',
            'data' => null,
        ], 200);
    }
  
  
  
    
};
