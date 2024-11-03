<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Attribute;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


use App\Helper\UtilityHelper;

class AttributeController extends AbstractController
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
     
     
    private function _createSingle($articleId, $key, $value): Response
    {
        $attribute = new Attribute();       
        
        $slug = strtolower($this->slugger->slug($key)->toString());
        
        $attribute->setArticleId($articleId);
        $attribute->setKey($key);
        $attribute->setSlug($slug);
        $attribute->setValue($value);

        $entityManager->persist($attribute);
        $entityManager->flush();                
    }
    
       
    public function create(Request $request): Response
    {
        $articleId = $request->request->get('articleId');
        $key = $request->request->get('key');
        $value = $request->request->get('value');
        
        if (!$key) {
            return $this->json([
                'error' => true,
                'data' => null,
                'message' => "Field 'key' is required",
            ], Response::HTTP_BAD_REQUEST);
        }
        
        if (!$value) {
            return $this->json([
                'error' => true,
                'data' => null,
                'message' => "Field 'value' is required",
            ], Response::HTTP_BAD_REQUEST);
        }
        
        if (!$articleId) {
            return $this->json([
                'error' => true,
                'data' => null,
                'message' => "Field 'articleId' is required",
            ], Response::HTTP_BAD_REQUEST);
        }
        
        $attribute = new Attribute();       
        
        $slug = strtolower($this->slugger->slug($key)->toString());
        
        $attribute->setArticleId($articleId);
        $attribute->setKey($key);
        $attribute->setSlug($slug);
        $attribute->setValue($value);

        $entityManager->persist($attribute);
        $entityManager->flush();

        return $this->json([
            'error' => false,
            'data' => null,
            'message' => 'Attribute created successfully',
        ], Response::HTTP_CREATED);
        
    }
    
    
	public function getAttributesBySlugOld(Request $request, $slug): Response
	{
		//$slug = $request->request->get('slug');

		error_log( $slug );

		if (!$slug) {
			return $this->json([
				'error' => true,
				'data' => null,
				'message' => "Missing 'slug' parameter",
			], Response::HTTP_BAD_REQUEST);
		}

		$attributes = $this->entityManager->getRepository(Attribute::class)
			->findBy(['slug' => $slug]);

		return $this->json([
			'error' => false,
			'data' => $attributes,
			'message' => count($attributes) > 0 ? 'Attributes fetched successfully' : 'No attributes found with the given slug',
		], Response::HTTP_OK);
	}


    public function getAttributesBySlug($slug): Response
    {
        try {
            // Ensure articleId is valid
            if (!$slug) {
                return $this->json(['error' => 'No slug in URL!'], Response::HTTP_BAD_REQUEST);
            }

            $attributeRepository = $this->entityManager->getRepository(Attribute::class);
            $queryBuilder = $attributeRepository->createQueryBuilder('a');

            $queryBuilder->andWhere('a.slug = :slug')
                         ->setParameter('slug', $slug);

            $attributes = $queryBuilder->getQuery()->getResult();

            $attributeData = [];
            $count = 0;
            foreach ($attributes as $attribute) {
                $count++;
                $attributeData[] = [
                    'id' => $attribute->getId(), 
                    'articleId' => $attribute->getArticleId(),
                    'key' => $attribute->getKey(),
                    'value' => $attribute->getValue(),
                    'slug' => $attribute->getSlug(),
                ];
            }

            return $this->json([
                'data'=>[ 
                    'attributes' => $attributeData, 
                    'count' => $count ],
                'error' => false,
                'message' => null,
                ]);
                
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
       



/*
    public function createMulti(Request $request, EntityManagerInterface $em): Response
    {
        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);


        if (!$data || !isset($data['articleID'], $data['items']) || !is_array($data['items'])) {
            return $this->json([
                'error' => true,
                'data' => null,
                'message' => "Invalid JSON structure or missing required fields",
            ], Response::HTTP_BAD_REQUEST);
        }

        $articleId = $data['articleID'];

        // Delete existing attributes for the specified articleId
        $em->createQueryBuilder()
           ->delete(Attribute::class, 'a')
           ->where('a.articleId = :articleId')
           ->setParameter('articleId', $articleId)
           ->getQuery()
           ->execute();

        foreach ($data['items'] as $item) {
            if (!is_array($item) || count($item) !== 1) {
                return $this->json([
                    'error' => true,
                    'data' => null,
                    'message' => "Each item must be a key-value pair array",
                ], Response::HTTP_BAD_REQUEST);
            }

            foreach ($item as $key => $value) {
                $attribute = new Attribute();
                $attribute->setArticleId($articleId);
                $attribute->setKey($key);
                $attribute->setValue($value);
                $attribute->setSlug(strtolower($this->slugger->slug($key)->toString()));
                
                $em->persist($attribute);
            }
        }

        $em->flush();

        return $this->json([
            'error' => false,
            'data' => null,
            'message' => 'Attributes added successfully',
        ], Response::HTTP_CREATED);
    }
*/



    public function createMulti(Request $request, EntityManagerInterface $em): Response
    {
        $jsonData = $request->request->get('data');
        $data = json_decode($jsonData, true);

        // Basic validation
        if (!$data || !isset($data['articleID'], $data['items']) || !is_array($data['items'])) {
            return $this->json([
                'error' => true,
                'data' => null,
                'message' => "Invalid JSON structure or missing required fields",
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate each item to ensure it's a valid key-value pair array
        foreach ($data['items'] as $item) {
            if (!is_array($item) || count($item) !== 2) {
                return $this->json([
                    'error' => true,
                    'data' => null,
                    'message' => "Each item must be an array with exactly two elements (key and value).",
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $articleId = $data['articleID'];

        // Delete existing attributes for the specified articleId
        $em->createQueryBuilder()
           ->delete(Attribute::class, 'a')
           ->where('a.articleId = :articleId')
           ->setParameter('articleId', $articleId)
           ->getQuery()
           ->execute();

        // Process each item as a key-value pair and persist to database
        foreach ($data['items'] as $item) {
            $key = $item[0];
            $value = $item[1];

            $attribute = new Attribute();
            $attribute->setArticleId($articleId);
            $attribute->setKey($key);
            $attribute->setValue($value);
            $attribute->setSlug(strtolower($this->slugger->slug($key)->toString()));
            
            $em->persist($attribute);
        }

        $em->flush();

        return $this->json([
            'error' => false,
            'data' => null,
            'message' => 'Attributes added successfully',
        ], Response::HTTP_CREATED);
    }




    public function getNew(Request $request): Response
    {
        try {
            $perPage = $request->query->getInt('per-page', 0); 
            $page = $request->query->getInt('page', 1);
            $sort = $request->query->get('sort', 'desc'); 
            $search = $request->query->get('search'); 
            $articleId = $request->query->getInt('article-id', 0); 
            $offset = ($page - 1) * $perPage;

            // Determine the sorting field and direction
            switch ($sort) {
                case 'id-asc':
                    $sortField = 'id';
                    $sortDirection = 'ASC';
                    break;
                case 'id-desc':
                    $sortField = 'id';
                    $sortDirection = 'DESC';
                    break;
                case 'asc':
                    $sortField = 'key';
                    $sortDirection = 'ASC';
                    break;
                default:
                    $sortField = 'key';
                    $sortDirection = 'DESC';
                    break;
            }

            $attributeRepository = $this->entityManager->getRepository(Attribute::class);
            $queryBuilder = $attributeRepository->createQueryBuilder('a');

            // Apply search filter to key or value
            if (!empty($search)) {
                $queryBuilder->andWhere('a.key LIKE :search OR a.value LIKE :search')
                             ->setParameter('search', '%' . $search . '%');
            }

            // Apply articleId filter if provided
            if ($articleId !== 0) {
                $queryBuilder->andWhere('a.articleId = :articleId')
                             ->setParameter('articleId', $articleId);
            }

            // First, count the total matching results
            $totalQuery = clone $queryBuilder;
            $totalAttributes = count($totalQuery->getQuery()->getResult());

            // Apply pagination and sorting
            if ($perPage > 0) {
                $queryBuilder->orderBy('a.' . $sortField, $sortDirection)
                             ->setFirstResult($offset)
                             ->setMaxResults($perPage);
            } else {
                $queryBuilder->orderBy('a.' . $sortField, $sortDirection);
            }

            $attributes = $queryBuilder->getQuery()->getResult();

            // Prepare attribute data for response
            $attributeData = [];
            foreach ($attributes as $attribute) {
                $attributeData[] = [
                 //   'id' => $attribute->getId(),
                 //   'articleId' => $attribute->getArticleId(),
                    'key' => $attribute->getKey(),
                    'value' => $attribute->getValue(),
                ];
            }

            // Handle pagination data
            $paginateData = ($perPage > 0)
                ? $this->utilityHelper->paginate($totalAttributes, $perPage, $page, 3)
                : null;

            return $this->json(['total' => $totalAttributes, 'attributes' => $attributeData, 'paginateData' => $paginateData]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



   
    public function get(Request $request): Response
    {
        
        try {
            $perPage = $request->query->getInt('per-page', 0); 
            $page = $request->query->getInt('page', 1);
            $sort = $request->query->get('sort', 'desc'); 
            $search = $request->query->get('search'); 
            $articleId = $request->query->getInt('article-id', 0); 
            $offset = ($page - 1) * $perPage;

            // Determine the sorting order based on the "sort" parameter
            if ($sort === 'id-asc') {
                $order = ['id' => 'ASC'];
            } elseif ($sort === 'id-desc') {
                $order = ['id' => 'DESC'];
            } else {
                $order = ($sort === 'asc') ? ['key' => 'ASC'] : ['key' => 'DESC'];
            }

            // Safeguard: Ensure key($order) is always a valid string
            $sortField = key($order) !== null ? key($order) : 'id';
            $sortDirection = current($order);

            $attributeRepository = $this->entityManager->getRepository(Attribute::class);
            $queryBuilder = $attributeRepository->createQueryBuilder('a');

            // Apply search filter to key or value
            if (!empty($search)) {
                $queryBuilder->andWhere('a.key LIKE :search OR a.value LIKE :search')
                             ->setParameter('search', '%' . $search . '%');
            }

            // Apply articleId filter if provided
            if ($articleId !== 0) {
                $queryBuilder->andWhere('a.articleId = :articleId')
                             ->setParameter('articleId', $articleId);
            }

            // First, count the total matching results
            $totalQuery = clone $queryBuilder;
            $totalAttributes = count($totalQuery->getQuery()->getResult());

            // Apply pagination and sorting
            if ($perPage > 0) {
                $queryBuilder->orderBy('a.' . $sortField, $sortDirection)
                             ->setFirstResult($offset)
                             ->setMaxResults($perPage);
            } else {
                $queryBuilder->orderBy('a.' . $sortField, $sortDirection);
            }

            $attributes = $queryBuilder->getQuery()->getResult();

            // Prepare attribute data for response
            $attributeData = [];
            foreach ($attributes as $attribute) {
                $attributeData[] = [
                    'id' => $attribute->getId(), 
                    'articleId' => $attribute->getArticleId(),
                    'key' => $attribute->getKey(),
                    'value' => $attribute->getValue(),
                    'slug' => $attribute->getSlug(),
                ];
            }

            // Handle pagination data
            $paginateData = ($perPage > 0)
                ? $this->utilityHelper->paginate($totalAttributes, $perPage, $page, 3)
                : null;

            return $this->json(['total' => $totalAttributes, 'attributes' => $attributeData, 'paginateData' => $paginateData]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        
    }

   
   
    public function getByArticleId(int $articleId): Response
    {
        try {
            // Ensure articleId is valid
            if ($articleId === 0) {
                return $this->json(['error' => 'Invalid article ID'], Response::HTTP_BAD_REQUEST);
            }

            $attributeRepository = $this->entityManager->getRepository(Attribute::class);
            $queryBuilder = $attributeRepository->createQueryBuilder('a');

            $queryBuilder->andWhere('a.articleId = :articleId')
                         ->setParameter('articleId', $articleId);

            $attributes = $queryBuilder->getQuery()->getResult();

            $attributeData = [];
            $count = 0;
            foreach ($attributes as $attribute) {
                $count++;
                $attributeData[] = [
                    'id' => $attribute->getId(), 
                    'articleId' => $attribute->getArticleId(),
                    'key' => $attribute->getKey(),
                    'value' => $attribute->getValue(),
                    'slug' => $attribute->getSlug(),
                ];
            }

            return $this->json([
                'data'=>[ 
                    'attributes' => $attributeData, 
                    'count' => $count ],
                'error' => false,
                'message' => null,
                ]);
                
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
        
        
    public function getById(Request $request, $id): Response
    {
        $attributeRepository = $this->entityManager->getRepository(Attribute::class);
        $attribute = $attributeRepository->findOneBy(['id' => $id]);
		
		if (!$attribute) {
			throw new NotFoundHttpException('The attribute was not found.');
		}
        
        return $this->json([
            'data'=>[ 
                "id" => $id,
                "key" => $attribute->getKey(),
                "value" => $attribute->getValue(),
                "slug" => $attribute->getSlug(),
                "articleId" => $attribute->getArticleId(),
            ],
            'error' => false,
            'message' => null,
        ]);   
    }
        
        
        
        
    
};
