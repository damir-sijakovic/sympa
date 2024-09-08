<?php

namespace App\Controller;

use App\Entity\Article;
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

        $this->entityManager->remove($article);
        $this->entityManager->flush();

        return $this->json(['message' => 'Article deleted successfully.']);
    }
    
    
    public function getArticlesPost(Request $request): Response
    {
        try {
            $perPage = $request->query->getInt('per_page', 10);
            $page = $request->query->getInt('page', 1);
            $sort = $request->query->get('sort', 'desc'); // Default sort order is descending
            $active = $request->query->get('active'); // Get the active parameter
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

            $totalArticles = $articleRepository->count($criteria);
            $articles = $articleRepository->findBy($criteria, $order, $perPage, $offset);

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



    
    /*
    
    
    public function getArticlesPost3(Request $request): Response
{
    try {
        $perPage = $request->query->getInt('per_page', 10);
        $page = $request->query->getInt('page', 1);
        $sort = $request->query->get('sort', 'desc'); // Default sort order is descending
        $active = $request->query->get('active'); // Get the active parameter
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

        // Create criteria for the query, including active filter if provided
        $criteria = [];
        if ($active === '1') {
            $criteria['active'] = true;
        } elseif ($active === '0') {
            $criteria['active'] = false;
        }

        $totalArticles = $articleRepository->count($criteria);
        $articles = $articleRepository->findBy($criteria, $order, $perPage, $offset);

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

    
    
    public function getArticlesPost1(Request $request): Response
    {
        try {
            $perPage = $request->query->getInt('per_page', 10);
            $page = $request->query->getInt('page', 1);
            $sort = $request->query->get('sort', 'desc'); // Default sort order is descending
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

            $totalArticles = $articleRepository->count([]);
            $articles = $articleRepository->findBy([], $order, $perPage, $offset);

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
    
    
    public function getArticlesPost0(Request $request): Response
	{
		try {
			$perPage = $request->query->getInt('per_page', 10);
			$page = $request->query->getInt('page', 1);
			$sort = $request->query->get('sort', 'desc'); // Default sort order is descending
			$offset = ($page - 1) * $perPage;

			$articleRepository = $this->entityManager->getRepository(Article::class);
			$order = ($sort === 'asc') ? ['createdAt' => 'ASC'] : ['createdAt' => 'DESC'];

			$totalArticles = $articleRepository->count([]);
			$articles = $articleRepository->findBy([], $order, $perPage, $offset);

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
    */
    
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
