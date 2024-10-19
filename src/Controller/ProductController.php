<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleTag;
use App\Entity\ArticleCategory;
use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\Attribute;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

use App\Helper\UtilityHelper;

class ProductController extends AbstractController
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
     
    private function _deleteAttributesByArticleId(int $articleId): void
    {
        $attributes = $this->entityManager->getRepository(Attribute::class)->findBy(['articleId' => $articleId]);

        foreach ($attributes as $attribute) {
            $this->entityManager->remove($attribute);
        }

        $this->entityManager->flush();
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
    
    
    private function _removeLinksForUpdate(Article $article): void
    {
        $articleTags = $this->entityManager->getRepository(ArticleTag::class)->findBy(['article' => $article]);
        foreach ($articleTags as $articleTag) {
            $this->entityManager->remove($articleTag);
        }

        $articleCategories = $this->entityManager->getRepository(ArticleCategory::class)->findBy(['article' => $article]);
        foreach ($articleCategories as $articleCategory) {
            $this->entityManager->remove($articleCategory);
        }
        
        $articleId = $article->getId();
        $attributes = $this->entityManager->getRepository(Attribute::class)->findBy(['articleId' => $articleId]);

        foreach ($attributes as $attribute) {
            $this->entityManager->remove($attribute);
        }

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
    
    
    public function removeArticleCategoryLinksPost(Request $request): Response
    {        
        $ids = $request->request->get('ids'); 
        
        // Check if 'ids' is provided
        if (!$ids) {
            throw new NotFoundHttpException('The article IDs were not found.');
        }
        
        // Convert the 'ids' string into an array of integers
        $idsArray = array_map('trim', explode(',', $ids));

        // Validate that each ID is numeric
        foreach ($idsArray as $id) {
            if (!is_numeric($id)) {
                throw new NotFoundHttpException('Invalid article ID: ' . $id);
            }
        }

        $articleRepository = $this->entityManager->getRepository(Article::class);
        
        $categoryIds = [];       

        foreach ($idsArray as $id) {
            // Find the article
            $article = $articleRepository->findOneBy(['id' => $id]);
            
            if (!$article) {
                throw new NotFoundHttpException('The article with ID ' . $id . ' was not found.');
            }

            // Find and remove all associated ArticleCategory links
            $articleCategories = $this->entityManager->getRepository(ArticleCategory::class)->findBy(['article' => $article]);
            foreach ($articleCategories as $articleCategory) {
                $categoryIds[] = $articleCategory->getId(); // Collect the category ID
                $this->entityManager->remove($articleCategory);
            }            
          
        }

        // Flush the changes to the database
        $this->entityManager->flush();

        // Return the JSON response
        return $this->json([
            'data' => ['categoryIds' => $categoryIds],
            'error' => false,
            'message' => '',
        ], 200);
    }

    
       
    public function removeArticleTagLinksPost(Request $request): Response
    {        
        $ids = $request->request->get('ids'); 
        
        // Check if 'ids' is provided
        if (!$ids) {
            throw new NotFoundHttpException('The article IDs were not found.');
        }
        
        $idsArray = array_map('trim', explode(',', $ids));

        foreach ($idsArray as $id) {
            if (!is_numeric($id)) {
                throw new NotFoundHttpException('Invalid article ID: ' . $id);
            }
        }

        $articleRepository = $this->entityManager->getRepository(Article::class);
        
        $tagIds = []; 

        foreach ($idsArray as $id) {
            $article = $articleRepository->findOneBy(['id' => $id]);            
            if (!$article) {
                throw new NotFoundHttpException('The article with ID ' . $id . ' was not found.');
            }

            $articleTags = $this->entityManager->getRepository(ArticleTag::class)->findBy(['article' => $article]);
            foreach ($articleTags as $articleTag) {
                $tagIds[] = $articleTag->getId(); // Collect the tag ID
                $this->entityManager->remove($articleTag);
            }
        }

        $this->entityManager->flush();

        return $this->json([
            'data' => ['tagIds' => $tagIds],
            'error' => false,
            'message' => '',
        ], 200);
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
    
    

public function getProductsPost(Request $request): Response
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
            ->setParameter('type', 'product');

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

        // Clone the query builder for counting results to avoid affecting the main query
        $totalQueryBuilder = clone $queryBuilder;
        $totalArticles = (int) $totalQueryBuilder->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();

        // Log the pagination values for debugging
        //error_log("Page: " . $page);
        //error_log("PerPage: " . $perPage);
        //error_log("Offset: " . $offset);
        //error_log("Total Articles: " . $totalArticles);

        // Ensure offset doesn't exceed total articles
        if ($offset >= $totalArticles) {
            $offset = max(0, $totalArticles - $perPage);  // Adjust offset if it exceeds total
            //error_log("Adjusted Offset: " . $offset);  // Log the adjusted offset
        }

        // Apply pagination and sorting
        if (!empty($order)) {
            $queryBuilder->orderBy('a.' . key($order), current($order));
        }

        $queryBuilder->setFirstResult($offset)->setMaxResults($perPage);

        // Fetch articles after count, this should return Article entities
        $articles = $queryBuilder->getQuery()->getResult();

        //if (empty($articles)) {
        //    error_log("No articles found for the current page and offset.");
        //} else {
        //    error_log("Found " . count($articles) . " articles.");
        //}

        // Prepare article data for response
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

        return $this->json(['total' => $totalArticles, 'products' => $articleData, 'paginateData' => $paginateData]);
    } 
    catch (\Exception $e) 
    {
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
                ->setParameter('type', 'product');

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
                'message' => 'No IDs provided.',
                'data' => null
            ], 400);
        }

        $idsArray = array_map('trim', explode(',', $ids));

        // Validate each ID is numeric
        foreach ($idsArray as $id) {
            if (!is_numeric($id)) {
                return $this->json([
                    'error' => true,
                    'message' => 'Invalid ID format.',
                    'data' => null
                ], 400);
            }
        }

        // Fetch all articles with IDs in $idsArray
        $articles = $this->entityManager->getRepository(Article::class)->findBy(['id' => $idsArray]);

        if (!$articles) {
            return $this->json([
                'error' => true,
                'message' => 'No articles found for the provided IDs.',
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
            'message' => 'Articles deleted successfully.',
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
                'message' => 'No ID\'s provided.', 
                'data' => 'user-not-logged-on'
            ], 400);
        }

        $idsArray = array_map('trim', explode(',', $ids));

        // Validate each ID is numeric
        foreach ($idsArray as $id) {
            if (!is_numeric($id)) {
                return $this->json([
                    'error' => true,
                    'message' => 'No ID\'s provided.', 
                    'data' => 'user-not-logged-on'
                ], 400);
            }
        }

        $articles = $this->entityManager->getRepository(Article::class)->findBy(['id' => $idsArray]);

        if (!$articles) {
            return $this->json([
                'error' => true,
                'message' => 'No article\'s by ID found.', 
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
        $title = $request->request->get('name');
        $slug = strtolower($this->slugger->slug($title)->toString());
        $excerpt = $request->request->get('shortDescription');
        $content = $request->request->get('description');
        $contentNoEscape = $this->utilityHelper->removeNewLines($content);
        $active = ($request->request->get('active') === "true");
        $image = $request->request->get('image');        
        $sku = $request->request->get('sku');        
        $price = floatval($request->request->get('price'));
        $quantity = intval($request->request->get('quantity'));
        $metaDescription = $request->request->get('metaDescription');
        $ogTitle = $request->request->get('ogTitle');
        $ogDescription = $request->request->get('ogDescription');
        $ogUrl = $request->request->get('ogUrl');
        $uuid = $request->request->get('uuid');
        $groupUuid = $request->request->get('groupUuid');  
        
        if ($groupUuid === "") $groupUuid = null;
        
        $metaJson = $request->request->get('metaJson');  
        $metaJsonData = [];      
        if ($metaJson){
            $metaJsonData = json_decode($metaJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json([
                    'error' => true,
                    'message' => 'Invalid JSON',
                    'data' => null,
                ], 400);
            }        
        }
        
        // Create new Article
        $article = new Article();
        $article->setTitle($title)
                ->setSlug($slug)
                ->setContent($contentNoEscape)
                ->setExcerpt($excerpt)
                ->setActive($active)
                ->setImage($image)
                ->setPrice($price)
                ->setSku($sku)
                ->setQuantity($quantity)
                ->setMetadata($metaJsonData)
                ->setMetaDescription($metaDescription)
                ->setOgTitle($ogTitle)
                ->setOgDescription($ogDescription)
                ->setOgUrl($ogUrl)
                ->setUuid($uuid)
                ->setType('product')
                ->setAuthor("administrator")
                ->setGroupUuid($groupUuid)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setModifiedAt(new \DateTimeImmutable());

        // Persist Article
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        // Link Categories to Article
        $categoryIds = $request->request->get('categories');
        if ($categoryIds) {
            $idsArray = array_map('trim', explode(',', $categoryIds));
            foreach ($idsArray as $id) {
                $category = $this->entityManager->getRepository(Category::class)->find($id);
                if ($category) {
                    $articleCategory = new ArticleCategory();
                    $articleCategory->setArticle($article)
                                    ->setCategory($category)
                                    ->setType('product');
                    $this->entityManager->persist($articleCategory);
                }
            }
        }

        // Link Tags to Article
        $tagIds = $request->request->get('tags');
        if ($tagIds) {
            $idsArray = array_map('trim', explode(',', $tagIds));
            foreach ($idsArray as $id) {
                $tag = $this->entityManager->getRepository(Tag::class)->find($id);
                if ($tag) {
                    $articleTag = new ArticleTag();
                    $articleTag->setArticle($article)
                               ->setTag($tag)
                               ->setType('product');
                    $this->entityManager->persist($articleTag);
                }
            }
        }


         // Link Attributes to Article
         $attributes = $request->request->get('attributes'); 
         if ($attributes) {
             $decodedData = json_decode($attributes, true);
             foreach ($decodedData as $key => $value) {
                 $attribute = new Attribute();
                 $attribute->setArticleId($article->getId())
                           ->setKey($key)
                           ->setValue($value);
                 $this->entityManager->persist($attribute);
             }
         }

        // Save all changes
        $this->entityManager->flush();

        return $this->json([
            'error' => false,
            'message' => 'Article created successfully',
            'article_id' => $article->getId(),
        ], 201);
    }

  

    public function updatePost(Request $request): Response
    {
        $id = $request->request->get('id');
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            return $this->json([
                'error' => true,
                'message' => 'Article not found',
            ], 404);
        }

        $title = $request->request->get('name');
        $slug = strtolower($this->slugger->slug($title)->toString());
        $excerpt = $request->request->get('shortDescription');
        $content = $request->request->get('description');
        $contentNoEscape = $this->utilityHelper->removeNewLines($content);
        $active = ($request->request->get('active') === "true");
        $image = $request->request->get('image');        
        $sku = $request->request->get('sku');        
        $price = floatval($request->request->get('price'));
        $quantity = intval($request->request->get('quantity'));
        $metaDescription = $request->request->get('metaDescription');
        $ogTitle = $request->request->get('ogTitle');
        $ogDescription = $request->request->get('ogDescription');
        $ogUrl = $request->request->get('ogUrl');
        $uuid = $request->request->get('uuid');
        $groupUuid = $request->request->get('groupUuid');
        $metaJson = $request->request->get('metaJson');  
        $metaJsonData = [];      

        if ($metaJson){
            $metaJsonData = json_decode($metaJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json([
                    'error' => true,
                    'message' => 'Invalid JSON',
                    'data' => null,
                ], 400);
            }        
        }

        // Update Article
        $article->setTitle($title)
                ->setSlug($slug)
                ->setContent($contentNoEscape)
                ->setExcerpt($excerpt)
                ->setActive($active)
                ->setImage($image)
                ->setPrice($price)
                ->setSku($sku)
                ->setQuantity($quantity)
                ->setMetadata($metaJsonData)
                ->setMetaDescription($metaDescription)
                ->setOgTitle($ogTitle)
                ->setOgDescription($ogDescription)
                ->setOgUrl($ogUrl)
                ->setUuid($uuid)
                ->setGroupUuid($groupUuid)
                ->setModifiedAt(new \DateTimeImmutable());


        $this->_removeLinksForUpdate($article);

        // Link Categories to Article
        $categoryIds = $request->request->get('categories');
        if ($categoryIds) {
          
            $idsArray = array_map('trim', explode(',', $categoryIds));            
            foreach ($idsArray as $id) {
                $category = $this->entityManager->getRepository(Category::class)->find($id);
                if ($category) {
                    $articleCategory = new ArticleCategory();
                    $articleCategory->setArticle($article)
                                    ->setCategory($category)
                                    ->setType('product');
                    $this->entityManager->persist($articleCategory);
                }
            }        
        }

        // Link Tags to Article
        $tagIds = $request->request->get('tags');
        if ($tagIds) {
            $idsArray = array_map('trim', explode(',', $tagIds));
            foreach ($idsArray as $id) {
                $tag = $this->entityManager->getRepository(Tag::class)->find($id);
                if ($tag) {
                    $articleTag = new ArticleTag();
                    $articleTag->setArticle($article)
                               ->setTag($tag)
                               ->setType('product');
                    $this->entityManager->persist($articleTag);
                }
            }       
        }

        // Link Attributes to Article
        $attributes = $request->request->get('attributes'); 
        if ($attributes) {      
            $decodedData = json_decode($attributes, true);
            foreach ($decodedData as $key => $value) {
                $attribute = new Attribute();
                $attribute->setArticleId($article->getId())
                          ->setKey($key)
                          ->setValue($value);
                $this->entityManager->persist($attribute);
            }
        }

        // Save all changes
        $this->entityManager->flush();

        return $this->json([
            'error' => false,
            'message' => 'Article updated successfully',
            'article_id' => $article->getId(),
        ], 200);
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
