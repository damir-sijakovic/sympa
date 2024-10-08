<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Article;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

use DirectoryIterator;
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
		$articles = $this->renderView('dashboard/home.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
            'userId' => $session->get('userId'),
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
     
    public function products(Request $request): Response
    {
        $session = $request->getSession();	
		$articles = $this->renderView('dashboard/product-list.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
            'userId' => $session->get('userId'),
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
       
       
    public function createProduct(Request $request): Response
    {
        $session = $request->getSession();	
        
        $components = $this->renderView('dashboard/components/select-image.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(), 
        ]);
        
		$articles = $this->renderView('dashboard/product-create.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
			'imagesUrl' => $this->utilityHelper->getRootUrl() .'/uploads/images'  ,
            'controller_name' => 'DashboardController',
            'uuid' => $this->utilityHelper->generateUuid(),
            'groupUuid' => $this->utilityHelper->generateUuid(),
            'components' => $components,
        ]);
		
        return $this->render('dashboard/main.html.twig', [
            'userId' => $session->get('userId'),  
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }     
       
       
    public function orders(Request $request): Response
    {
        $session = $request->getSession();	
		$articles = $this->renderView('dashboard/orders.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
            'userId' => $session->get('userId'),
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
       
       
    public function articles(Request $request): Response
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
            'active' => $article->isActive() ? 'true' : 'false',
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
  
  
    public function productCategories(Request $request): Response
    {
        $session = $request->getSession();	
		$articles = $this->renderView('dashboard/product-category-list.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
            'userId' => $session->get('userId'), 
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
  

    public function productTags(Request $request): Response
    {
        $session = $request->getSession();	
		$articles = $this->renderView('dashboard/product-tag-list.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
            'userId' => $session->get('userId'), 
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
  

    public function categories(Request $request): Response
    {
        $session = $request->getSession();	
		$articles = $this->renderView('dashboard/category-list.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
            'userId' => $session->get('userId'), 
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
  
  
    public function tags(Request $request): Response
    {
        $session = $request->getSession();	
		$articles = $this->renderView('dashboard/tag-list.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
            'userId' => $session->get('userId'), 
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
  
    public function images(Request $request): Response
    {
        $session = $request->getSession();	
		$articles = $this->renderView('dashboard/images.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
            'userId' => $session->get('userId'), 
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
  
    public function files(Request $request): Response
    {
        $session = $request->getSession();	
		$articles = $this->renderView('dashboard/files.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
            'userId' => $session->get('userId'), 
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
  
  
    public function createCategory(Request $request): Response
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
  
    public function createTag(Request $request): Response
    {
        $session = $request->getSession();	
        
		$articles = $this->renderView('dashboard/tag-create.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'userId' => $session->get('userId'),  
            'body' => $articles,
        ]);
    }
  
  
    public function createProductCategory(Request $request): Response
    {
		$articles = $this->renderView('dashboard/product-category-create.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $articles,
        ]);
    }
  
  
    public function createProductTag(Request $request): Response
    {
        $session = $request->getSession();	
        
		$articles = $this->renderView('dashboard/product-tag-create.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
		
        return $this->render('dashboard/main.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'userId' => $session->get('userId'),  
            'body' => $articles,
        ]);
    }
    

    public function editCategory(Request $request, $id): Response
    {
        $session = $request->getSession();	
        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $category = $categoryRepository->findOneBy(['id' => $id]);

        if (!$category) {
            throw new NotFoundHttpException('The category was not found.');
        }

        $components = $this->renderView('dashboard/components/select-image.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(), 
        ]);

        $categories = $this->renderView('dashboard/category-edit.html.twig', [
            'id' => $id,
            'components' => $components, 
            'rootUrl' => $this->utilityHelper->getRootUrl(), 
            'name' => $category->getName(),   
            'slug' => $category->getSlug(), 
            'parent' => $category->getParent(),
            'description' => $category->getDescription(),
            'image' => $category->getImage(),
            'type' => $category->getType(),
            'visible' => $category->getVisible() ? 'yes' : 'no',
        ]);

        return $this->render('dashboard/main.html.twig', [ 
            'userId' => $session->get('userId'),           
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $categories,
        ]);
    }
  

    
    public function editTag(Request $request, $id): Response
    {
        $session = $request->getSession();	
        $tagRepository = $this->entityManager->getRepository(Tag::class);
        $tag = $tagRepository->findOneBy(['id' => $id]);

        if (!$tag) {
            throw new NotFoundHttpException('The tag was not found.');
        }

        $tags = $this->renderView('dashboard/tag-edit.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(), 
            'name' => $tag->getName(),   
            'slug' => $tag->getSlug(), 
            'description' => $tag->getDescription(), 
            
            
        /*
            'rootUrl' => $this->utilityHelper->getRootUrl(),    
            'title' => $category->getTitle(),
            'slug' => $category->getSlug(),
            'excerpt' => $category->getExcerpt(),
            'content' => $category->getContent(),
            'image' => $category->getImage(),
            'type' => $category->getType(),
            'active' => $category->isActive() ? 'yes' : 'no',
            'id' => $id
            */
        ]);

        return $this->render('dashboard/main.html.twig', [ 
            'userId' => $session->get('userId'),           
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $tags,
        ]);
    }
  


    public function editImages(Request $request, $slug): Response
    {         
        $session = $request->getSession();	
        $imagesUrl = $this->utilityHelper->getRootUrl() . '/uploads/images';
        
		$body = $this->renderView('dashboard/images-edit.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
			'slug' => $slug,
			'imagesUrl' => $imagesUrl,
        ]);
		
        return $this->render('dashboard/main.html.twig', [ 
            'userId' => $session->get('userId'),           
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $body,
        ]);
    }
  
  
    public function editFiles(Request $request, $slug): Response
    {         
        $session = $request->getSession();	
        $filesUrl = $this->utilityHelper->getRootUrl() . '/uploads/files';
        
		$body = $this->renderView('dashboard/files-edit.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
			'slug' => $slug,
			'filesUrl' => $filesUrl,
        ]);
		
        return $this->render('dashboard/main.html.twig', [ 
            'userId' => $session->get('userId'),           
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $body,
        ]);
    }
  

    public function docs(Request $request): Response
    {         
        $session = $request->getSession();	
        
		$body = $this->renderView('dashboard/docs.html.twig', [
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'nonce' => $request->attributes->get('nonce'),
        ]);
		
        return $this->render('dashboard/main.html.twig', [ 
            'userId' => $session->get('userId'),           
			'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $body,            
        ]);
    }
  

    
    
    
};
