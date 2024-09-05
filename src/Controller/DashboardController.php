<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

use App\Helper\UtilityHelper;

class DashboardController extends AbstractController
{	
    
    private $entityManager;
	private $utilityHelper;
	
	public function __construct(
        UtilityHelper $utilityHelper,
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;	
		$request = Request::createFromGlobals();
		$rootUrl = $request->getSchemeAndHttpHost(); 		
		$this->utilityHelper = $utilityHelper;
	}
	
    
    public function index(Request $request): Response
    {
        $session = $request->getSession();	
		$articles = $this->renderView('dashboard/article-list.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
            'userId' => $session->get('userId'),
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
       
  
    public function editArticle(Request $request, $id): Response
    {
        $session = $request->getSession();	
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $article = $articleRepository->findOneBy(['id' => $id]);

        if (!$article) {
            throw new NotFoundHttpException('The article was not found.');
        }
        
        
		$articles = $this->renderView('dashboard/article-edit.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),

            'title' => $article->getTitle(),
            'slug' => $article->getSlug(),
            'excerpt' => $article->getExcerpt(),
            'content' => $article->getContent(),
            'image' => $article->getImage(),
            'type' => $article->getType(),
            'active' => $article->isActive() ? 'yes' : 'no',
            'id' => $id
        ]);
		
        return $this->render('dashboard/main.html.twig', [ 
            'userId' => $session->get('userId'),           
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
  
  
    
    public function createArticle(Request $request): Response
    {
        $session = $request->getSession();	
		$articles = $this->renderView('dashboard/article-create.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
            'userId' => $session->get('userId'),  
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
  
  
    
  
    public function categories(): Response
    {
		$articles = $this->renderView('dashboard/article-categories.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
  
  
    public function createCategory(): Response
    {
		$articles = $this->renderView('dashboard/category-create.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
  
  
    

  
    
    
    
};
