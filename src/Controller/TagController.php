<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Article;
use App\Entity\ArticleCategory;
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
        $tag->setType("article");

        $entityManager->persist($tag);
        $entityManager->flush();

        return $this->json([
            'error' => false,
            'data' => null,
            'message' => 'Tag created successfully',
        ], Response::HTTP_CREATED);
    }
    
    
};



        

