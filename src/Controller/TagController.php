<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Article;
use App\Entity\ArticleTag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

use App\Helper\UtilityHelper;

class TagController extends AbstractController
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

            $tagRepository = $this->entityManager->getRepository(Tag::class);

            $queryBuilder = $tagRepository->createQueryBuilder('t');

            if (!empty($search)) {
                $queryBuilder->andWhere('t.name LIKE :search OR t.slug LIKE :search')
                             ->setParameter('search', '%' . $search . '%');
            }

            $queryBuilder->andWhere('t.type = :type')
                         ->setParameter('type', $type);

            $totalQuery = clone $queryBuilder;
            $totalTags = count($totalQuery->getQuery()->getResult());

            if ($perPage > 0) {
                $queryBuilder->orderBy('t.' . key($order), current($order))
                             ->setFirstResult($offset)
                             ->setMaxResults($perPage);
            } else {
                $queryBuilder->orderBy('t.' . key($order), current($order));
            }

            $tags = $queryBuilder->getQuery()->getResult();

            $tagData = [];
            foreach ($tags as $tag) {
                $tagData[] = [
                    'id' => $tag->getId(),
                    'name' => $tag->getName(),
                    'slug' => $tag->getSlug()                   
                ];
            }

            $paginateData = ($perPage > 0)
                ? $this->utilityHelper->paginate($totalTags, $perPage, $page, 3)
                : null; 

            return $this->json(['total' => $totalTags, 'tags' => $tagData, 'paginateData' => $paginateData]);
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
            $search = $request->query->get('search'); // Get the search parameter
            $offset = ($page - 1) * $perPage;

            // Determine the sorting order based on the "sort" parameter
            if ($sort === 'id-asc') {
                $order = ['id' => 'ASC'];
            } elseif ($sort === 'id-desc') {
                $order = ['id' => 'DESC'];
            } else {
                // Default to sorting by name
                $order = ($sort === 'asc') ? ['name' => 'ASC'] : ['name' => 'DESC'];
            }

            $tagRepository = $this->entityManager->getRepository(Tag::class);

            // Create the query with filters
            $queryBuilder = $tagRepository->createQueryBuilder('t');

            // Apply search filter to name
            if (!empty($search)) {
                $queryBuilder->andWhere('t.name LIKE :search OR t.slug LIKE :search')
                             ->setParameter('search', '%' . $search . '%');
            }

            // First, count the total matching results
            $totalQuery = clone $queryBuilder;
            $totalTags = count($totalQuery->getQuery()->getResult());

            // Apply pagination and sorting
            $queryBuilder->orderBy('t.' . key($order), current($order))
                         ->setFirstResult($offset)
                         ->setMaxResults($perPage);

            $tags = $queryBuilder->getQuery()->getResult();

            // Prepare tag data for response
            $tagData = [];
            foreach ($tags as $tag) {
                $tagData[] = [
                    'id' => $tag->getId(),
                    'name' => $tag->getName(),
                    'slug' => $tag->getSlug()                   
                ];
            }

            $paginateData = $this->utilityHelper->paginate($totalTags, $perPage, $page, 3); // 3 = visible pages

            return $this->json(['total' => $totalTags, 'tags' => $tagData, 'paginateData' => $paginateData]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function edit(Request $request): Response
    {
       $id = $request->request->get('id');
        $name = $request->request->get('name');

        if (!$name) {
            return $this->json([
                'error' => true,
                'data' => null,
                'message' => 'Tag name is required',            
            ], Response::HTTP_BAD_REQUEST);
        }

        $tagRepository = $this->entityManager->getRepository(Tag::class);
        $tag = $id ? $tagRepository->find($id) : new Tag();

        if (!$tag) {
            return $this->json([
                'error' => true,
                'data' => null,
                'message' => 'Tag not found',            
            ], Response::HTTP_NOT_FOUND);
        }

        $slug = strtolower($this->slugger->slug($name)->toString());
        $tag->setName($name);        
        $tag->setSlug($slug);        
        
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        return $this->json([
                'error' => false,
                'data' => null,
                'message' => 'Tag saved successfully',            
            ]);
    }
    
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $name = $request->request->get('name');
        $type = $request->request->get('type', 'article');
        
        if (!$name) {
            return $this->json([
                'error' => true,
                'data' => null,
                'message' => 'Tag name is required',
            ], Response::HTTP_BAD_REQUEST);
        }

        $tag = new Tag();
        $slug = strtolower($this->slugger->slug($name)->toString());
        
        $tag->setName($name);
        $tag->setSlug($slug);
        $tag->setType($type);

        $entityManager->persist($tag);
        $entityManager->flush();

        return $this->json([
            'error' => false,
            'data' => null,
            'message' => 'Tag created successfully',
        ], Response::HTTP_CREATED);
    }
    
    public function getTagsByIds(Request $request): Response
    {
        try {
            // Get the "ids" string from the request, e.g., "11,1,34,5"
            $idsString = $request->request->get('ids');

            $ids = array_map('intval', explode(',', $idsString));

            if (empty($ids)) {
                return $this->json(['error' => 'No IDs provided.'], Response::HTTP_BAD_REQUEST);
            }

            $tagRepository = $this->entityManager->getRepository(Tag::class);

            $queryBuilder = $tagRepository->createQueryBuilder('t')
                                          ->where('t.id IN (:ids)')
                                          ->setParameter('ids', $ids);

            $tags = $queryBuilder->getQuery()->getResult();

            $tagData = [];
            foreach ($tags as $tag) {
                $tagData[] = [
                    'id' => $tag->getId(),
                    'name' => $tag->getName(),
                    'slug' => $tag->getSlug(),
                    'type' => $tag->getType(),
                ];
            }

            return $this->json(['tags' => $tagData]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function addArticleToTags(Request $request): Response
    {
        $articleId = $request->request->get('article_id');
        $tagIds = $request->request->get('tag_ids');

        if (!$articleId) {
            return $this->json([
                'error' => true,
                'message' => 'Article ID not provided',
                'data' => null,
            ], 400);
        }
        
        if (!$tagIds){
            $articleTags = $this->entityManager->getRepository(ArticleTag::class)->findBy(['article' => $articleId]);        
            foreach ($articleTags as $articleTag) {
                $this->entityManager->remove($articleTag);
            }
            $this->entityManager->flush();
            return $this->json([
                'error' => false,
                'message' => 'Article linked to tags successfully',
                'data' => null,
            ], 200);            
        }
   
            
        $tagIdsArray = array_map('trim', explode(',', $tagIds));
        $article = $this->entityManager->getRepository(Article::class)->find($articleId);

        if (!$article) {
            return $this->json([
                'error' => true,
                'message' => 'Article not found',
                'data' => null,
            ], 404);
        }
        
        //delete old links
        $articleTags = $this->entityManager->getRepository(ArticleTag::class)->findBy(['article' => $articleId]);        
        foreach ($articleTags as $articleTag) {
            $this->entityManager->remove($articleTag);
        }

        foreach ($tagIdsArray as $tagId) {
            if (!is_numeric($tagId)) {
                return $this->json([
                    'error' => true,
                    'message' => 'Invalid tag ID provided',
                    'data' => null,
                ], 400);
            }

            $tag = $this->entityManager->getRepository(Tag::class)->find($tagId);

            if (!$tag) {
                return $this->json([
                    'error' => true,
                    'message' => 'Tag with ID ' . $tagId . ' not found',
                    'data' => null,
                ], 404);
            }
 
            $articleTag = new ArticleTag();
            $articleTag->setArticle($article);
            $articleTag->setType('article');
            $articleTag->setTag($tag);
            $this->entityManager->persist($articleTag);
            
        }
                
        $this->entityManager->flush();

        return $this->json([
            'error' => false,
            'message' => 'Article linked to categories successfully',
            'data' => null,
        ], 200);
    }

    
};



        

