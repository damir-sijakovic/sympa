<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleTag;
use App\Entity\ArticleCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

use App\Helper\UtilityHelper;

class ArticleController extends AbstractController
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
     
    private function _getArticleCategoryAndTagIds(Article $article): array
    {
        $articleCategories = $this->entityManager->getRepository(ArticleCategory::class)->findBy(['article' => $article]);
        $categoryIds = array_map(fn($articleCategory) => $articleCategory->getCategory()->getId(), $articleCategories);

        $articleTags = $this->entityManager->getRepository(ArticleTag::class)->findBy(['article' => $article]);
        $tagIds = array_map(fn($articleTag) => $articleTag->getTag()->getId(), $articleTags);

        return [
            'categoryIds' => $categoryIds,
            'tagIds' => $tagIds,
        ];
    } 
     
    private function _removeArticleLinks(Article $article): void
    {
        // Remove ArticleTag links
        $articleTags = $this->entityManager->getRepository(ArticleTag::class)->findBy(['article' => $article]);
        foreach ($articleTags as $articleTag) {
            $this->entityManager->remove($articleTag);
        }

        // Remove ArticleCategory links
        $articleCategories = $this->entityManager->getRepository(ArticleCategory::class)->findBy(['article' => $article]);
        foreach ($articleCategories as $articleCategory) {
            $this->entityManager->remove($articleCategory);
        }

        // Persist and flush changes
        $this->entityManager->flush();
    }
    
    public function getArticleCategoryAndTagIds(Request $request, $id): Response
    {
        if (!$id)
        {
            return $this->json([
                'data' => null,
                'error' => true,
                'message' => 'The id article was not found.',
            ],404);
        }
        
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $article = $articleRepository->findOneBy(['id' => $id]);
        
        if (!$article) 
        {
            return $this->json([
                'data' => null,
                'error' => true,
                'message' => 'The article was not found.',
            ],404);
        }
        
        $articleCategories = $this->entityManager->getRepository(ArticleCategory::class)->findBy(['article' => $article]);
        $categoryIds = array_map(fn($articleCategory) => $articleCategory->getCategory()->getId(), $articleCategories);

        $articleTags = $this->entityManager->getRepository(ArticleTag::class)->findBy(['article' => $article]);
        $tagIds = array_map(fn($articleTag) => $articleTag->getTag()->getId(), $articleTags);

        return $this->json([
            'data' => ['categoryIds' => $categoryIds,'tagIds' => $tagIds],
            'error' => false,
            'message' => '',
        ],200);
    } 
    
    
    public function removeArticleCategoryLinksPost(Request $request, $id): void
    {        
        $id = $request->request->getInt('id'); 
        if (!$id)
        {
            throw new NotFoundHttpException('The id article was not found.');
        }
        
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $article = $articleRepository->findOneBy(['id' => $id]);
        
        if (!$article) 
        {
            throw new NotFoundHttpException('The article was not found.');
        }
        
        $articleCategories = $this->entityManager->getRepository(ArticleCategory::class)->findBy(['article' => $article]);
        foreach ($articleCategories as $articleCategory) {
            $this->entityManager->remove($articleCategory);
        }

        $this->entityManager->flush();
    }
    
     
    public function removeArticleTagLinksPost(Request $request, $id): void
    {
        $id = $request->request->getInt('id'); 
        if (!$id)
        {
            throw new NotFoundHttpException('The id article was not found.');
        }
        
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $article = $articleRepository->findOneBy(['id' => $id]);
        
        if (!$article) 
        {
            throw new NotFoundHttpException('The article was not found.');
        }
        
        $articleTags = $this->entityManager->getRepository(ArticleTag::class)->findBy(['article' => $article]);
        foreach ($articleTags as $articleTag) {
            $this->entityManager->remove($articleTag);
        }

        $this->entityManager->flush();
    }
     
     
     
    
    public function getByIdPost(Request $request, $id): Response
    {
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $article = $articleRepository->findOneBy(['id' => $id]);
		
		if (!$article) {
			throw new NotFoundHttpException('The article was not found.');
		}

        return $this->json([
            "title" => $article->getTitle(),
            "excerpt" => $article->getExcerpt(),
            "content" => $article->getContent(),
        ]);	      
    }
    
    public function deleteArticleByIdPost(Request $request): Response
    {
        $id = $request->request->getInt('id');       
         
        if (!$id)
        {
            throw new NotFoundHttpException('The id article was not found.');
        }
        
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $article = $articleRepository->findOneBy(['id' => $id]);

        if (!$article) 
        {
            throw new NotFoundHttpException('The article was not found.');
        }
        
        $this->_removeArticleLinks($article);

        $this->entityManager->remove($article);
        $this->entityManager->flush();

        return $this->json(['message' => 'Article deleted successfully.']);
    }
    
    
    
 public function getArticlesPost(Request $request): Response
{
    try {
        $perPage = $request->query->getInt('per-page', 10);
        $page = $request->query->getInt('page', 1);
        $sort = $request->query->get('sort', 'desc'); // Default sort order is descending
        $active = $request->query->get('active'); // Get the active parameter
        $search = $request->query->get('search'); // Get the search parameter
        $category = $request->query->get('category'); // Get the category parameter
        $tag = $request->query->get('tag'); // Get the tag parameter
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

        $articleRepository = $this->entityManager->getRepository(Article::class);

        // Create criteria for the query, including active filter and type "article"
        $queryBuilder = $articleRepository->createQueryBuilder('a')
            ->where('a.type = :type')
            ->setParameter('type', 'article');

        if ($active === '1') {
            $queryBuilder->andWhere('a.active = :active')->setParameter('active', true);
        } elseif ($active === '0') {
            $queryBuilder->andWhere('a.active = :active')->setParameter('active', false);
        }

        if (!empty($search)) {
            $queryBuilder->andWhere('a.title LIKE :search OR a.excerpt LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($category)) {
            // Join with ArticleCategory entity to filter by category
            $queryBuilder->join('App\Entity\ArticleCategory', 'ac', 'WITH', 'ac.article = a')
                         ->andWhere('ac.category = :category')
                         ->setParameter('category', $category);
        }

        if (!empty($tag)) {
            // Join with ArticleTag entity to filter by tag
            $queryBuilder->join('App\Entity\ArticleTag', 'at', 'WITH', 'at.article = a')
                         ->andWhere('at.tag = :tag')
                         ->setParameter('tag', $tag);
        }

        // First count all results that match the criteria
        $totalQuery = clone $queryBuilder;
        $totalArticles = count($totalQuery->getQuery()->getResult());

        // Apply pagination and sorting
        $queryBuilder->orderBy('a.' . key($order), current($order))
                     ->setFirstResult($offset)
                     ->setMaxResults($perPage);

        $articles = $queryBuilder->getQuery()->getResult();

        $articleData = [];
        foreach ($articles as $article) {
            $articleData[] = [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'excerpt' => $article->getExcerpt(),
                'content' => $article->getContent(),
                'slug' => $article->getSlug(),
                'image' => $article->getImage(),
                'created_at' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
                'modified_at' => $article->getModifiedAt() ? $article->getModifiedAt()->format('Y-m-d H:i:s') : null,
                'active' => $article->isActive(),
            ];
        }

        $paginateData = $this->utilityHelper->paginate($totalArticles, $perPage, $page, 3); // 3 = visible pages

        return $this->json(['total' => $totalArticles, 'articles' => $articleData, 'paginateData' => $paginateData]);
    } catch (\Exception $e) {
        return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}




    public function getArticlesPostOLD(Request $request): Response
    {
        try {
            $perPage = $request->query->getInt('per-page', 10);
            $page = $request->query->getInt('page', 1);
            $sort = $request->query->get('sort', 'desc'); // Default sort order is descending
            $active = $request->query->get('active'); // Get the active parameter
            $search = $request->query->get('search'); // Get the search parameter
            $category = $request->query->get('category'); // Get the category parameter
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

            $articleRepository = $this->entityManager->getRepository(Article::class);

            // Create criteria for the query, including active filter and type "article"
            $criteria = ['type' => 'article'];
            if ($active === '1') {
                $criteria['active'] = true;
            } elseif ($active === '0') {
                $criteria['active'] = false;
            }

            // Build the query with optional category filter
            $queryBuilder = $articleRepository->createQueryBuilder('a')
                ->where('a.type = :type')
                ->setParameter('type', 'article');

            if ($active === '1') {
                $queryBuilder->andWhere('a.active = :active')->setParameter('active', true);
            } elseif ($active === '0') {
                $queryBuilder->andWhere('a.active = :active')->setParameter('active', false);
            }

            if (!empty($search)) {
                $queryBuilder->andWhere('a.title LIKE :search OR a.excerpt LIKE :search')
                             ->setParameter('search', '%' . $search . '%');
            }

            if (!empty($category)) {
                // Join with ArticleCategory entity to filter by category
                $queryBuilder->join('App\Entity\ArticleCategory', 'ac', 'WITH', 'ac.article = a')
                             ->andWhere('ac.category = :category')
                             ->setParameter('category', $category);
            }

            // First count all results that match the criteria
            $totalQuery = clone $queryBuilder;
            $totalArticles = count($totalQuery->getQuery()->getResult());

            // Apply pagination and sorting
            $queryBuilder->orderBy('a.' . key($order), current($order))
                         ->setFirstResult($offset)
                         ->setMaxResults($perPage);

            $articles = $queryBuilder->getQuery()->getResult();

            $articleData = [];
            foreach ($articles as $article) {
                $articleData[] = [
                    'id' => $article->getId(),
                    'title' => $article->getTitle(),
                    'excerpt' => $article->getExcerpt(),
                    'content' => $article->getContent(),
                    'slug' => $article->getSlug(),
                    'image' => $article->getImage(),
                    'created_at' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
                    'modified_at' => $article->getModifiedAt() ? $article->getModifiedAt()->format('Y-m-d H:i:s') : null,
                    'active' => $article->isActive(),
                ];
            }

            $paginateData = $this->utilityHelper->paginate($totalArticles, $perPage, $page, 3); // 3 = visible pages

            return $this->json(['total' => $totalArticles, 'articles' => $articleData, 'paginateData' => $paginateData]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    

 public function deleteByIdsPostNew(Request $request): Response
    {
        $ids = $request->request->get('ids');

        if (!$ids) {
            return $this->json([
                'error' => true,
                'message' => 'No IDs provided',
                'data' => null
            ], 400);
        }

        $idsArray = array_map('trim', explode(',', $ids));

        // Validate each ID is numeric
        foreach ($idsArray as $id) {
            if (!is_numeric($id)) {
                return $this->json([
                    'error' => true,
                    'message' => 'Invalid ID format',
                    'data' => null
                ], 400);
            }
        }

        // Fetch all articles with IDs in $idsArray
        $articles = $this->entityManager->getRepository(Article::class)->findBy(['id' => $idsArray]);

        if (!$articles) {
            return $this->json([
                'error' => true,
                'message' => 'No articles found for the provided IDs',
                'data' => null
            ], 404);
        }

        // Loop over each article, remove associated ArticleTags and ArticleCategories, then remove the article
        foreach ($articles as $article) {

            // Remove related ArticleTags
            $articleTags = $this->entityManager->getRepository(ArticleTag::class)->findBy(['article' => $article]);
            foreach ($articleTags as $articleTag) {
                $this->entityManager->remove($articleTag);
            }

            // Remove related ArticleCategories
            $articleCategories = $this->entityManager->getRepository(ArticleCategory::class)->findBy(['article' => $article]);
            foreach ($articleCategories as $articleCategory) {
                $this->entityManager->remove($articleCategory);
            }

            // Finally remove the article itself
            $this->entityManager->remove($article);
        }

        $this->entityManager->flush();

        return $this->json([
            'error' => false,
            'message' => 'Articles deleted successfully',
            'data' => null
        ], 200);
    }





    public function deleteByIdsPost(Request $request): Response
    {
        /*
        $session = $request->getSession();
        if (!$session->has('userLoggedIn')) 
        {
            return new JsonResponse([
                'error' => true,
                'message' => 'User not logged on!', 
                'data' => 'user-not-logged-on'
            ]);
        }   
        */
        
        $ids = $request->request->get('ids');
        
        if (!$ids) {
            return $this->json([
                'error' => true,
                'message' => 'No ID\'s provided', 
                'data' => 'user-not-logged-on'
            ], 400);
        }

        $idsArray = array_map('trim', explode(',', $ids));

        // Validate each ID is numeric
        foreach ($idsArray as $id) {
            if (!is_numeric($id)) {
                return $this->json([
                    'error' => true,
                    'message' => 'No ID\'s provided', 
                    'data' => 'user-not-logged-on'
                ], 400);
            }
        }

        $articles = $this->entityManager->getRepository(Article::class)->findBy(['id' => $idsArray]);

        if (!$articles) {
            return $this->json([
                'error' => true,
                'message' => 'No article\'s by ID found', 
                'data' => 'no-articles-by-id-found'
            ], 404);
        }

        foreach ($articles as $article) {
               $articleCategories = $this->entityManager->getRepository(ArticleCategory::class)->findBy(['article' => $article]);
            foreach ($articleCategories as $articleCategory) {
                $this->entityManager->remove($articleCategory);
            }

            $articleTags = $this->entityManager->getRepository(ArticleTag::class)->findBy(['article' => $article]);
            foreach ($articleTags as $articleTag) {
                $this->entityManager->remove($articleTag);
            }

            $this->entityManager->remove($article);
        }

        $this->entityManager->flush();
        return $this->json([
            'error' => false,
            'message' => 'Message deleted successfully.', 
            'data' => 'message-deleted-successfully'
        ], 200);
    }

  
  
    public function createPost(Request $request): Response
	{          
		$title = $request->request->get('title');
		$slug = strtolower($this->slugger->slug($title)->toString());
		$excerpt = $request->request->get('excerpt');
		$content = $request->request->get('content');
        $contentNoEscape = $this->utilityHelper->removeNewLines($content);
     	
        if (empty($title) || strlen($title)<3 ) {
			return $this->json([
                'error' => true, 
                'message' => 'Title too short', 
                'data' => null
            ]);
		}

		$existingArticle = $this->entityManager->getRepository(Article::class)->findOneBy(['title' => $title]) ?: $this->entityManager->getRepository(Article::class)->findOneBy(['slug' => $slug]);
		if ($existingArticle){
			return $this->json([
                'error' => true, 
                'message' => 'A article with the same title already exists', 
                'data' => null
            ], 409);
		}
        
        $article = new Article();
		
		$article->setTitle($title)
			 ->setSlug($slug)
			 ->setContent($contentNoEscape)
			 ->setExcerpt($excerpt)
			 ->setType('article')
			 ->setImage('image.jpg')
			 ->setAuthor("administrator")
			 ->setCreatedAt(new \DateTimeImmutable())
			 ->setModifiedAt(new \DateTimeImmutable())
			 ->setActive($request->request->get('active', true)); // Default to true if not set

		$this->entityManager->persist($article);
        $this->entityManager->flush();
        
        return $this->json([
            'error' => false,
            'message' => 'Post added successfully',
            'data' => null,
        ], 201);    
	}
    
    
    public function viewById(Request $request, $id): Response
	{          
		$htmlTemplate = $this->renderView('/pages/home.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
        
        return $this->render('/pages/main.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $htmlTemplate,
        ]);
	}
    
    
    
    
    
}
