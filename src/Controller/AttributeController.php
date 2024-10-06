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


   
    
    
    
};
