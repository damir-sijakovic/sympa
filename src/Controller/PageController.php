<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Helper\UtilityHelper;

class PageController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $utilityHelper;
    
	public function __construct(EntityManagerInterface $entityManager, 
        UtilityHelper $utilityHelper)
    {
        $this->entityManager = $entityManager;	
        $this->utilityHelper = $utilityHelper;	
                
	}
    

    public function home(Request $request): Response
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
    
    
    public function viewProductById(Request $request, $id): Response
    {        
        
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $article = $articleRepository->findOneBy(['id' => $id]);
		
		if (!$article) {
			throw new NotFoundHttpException('The product was not found.');
		}
        
        $quantity = $article->getQuantity();
        if (!$quantity) $quantity = -1;
        
        $component = $this->renderView('/store/components/shopping-cart.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
        ]);  
        
        $htmlTemplate = $this->renderView('/pages/product.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'id' => $id,
            'title' => $article->getTitle(),
            'excerpt' => $article->getExcerpt(),
            'content' => $article->getContent(),
            'sku' => $article->getSku(),
            'price' => $article->getPrice(),
            'image' => $article->getImage(),
            'quantity' => $quantity,
            'components' => $component,
        ]);

        $response = $this->render('/pages/main-clean.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $htmlTemplate,
        ]);

        $response->headers->set('Content-Type', 'text/html');

        return $response;
    }
    
    public function viewProductBySlug(Request $request, $slug): Response
    {
        
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $article = $articleRepository->findOneBy(['slug' => $slug]);
		
		if (!$article) {
			throw new NotFoundHttpException('The product was not found.');
		}
        
        $quantity = $article->getQuantity();
        if (!$quantity) $quantity = -1;
        
        $htmlTemplate = $this->renderView('/pages/product.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'id' => $article->getId(),
            'title' => $article->getTitle(),
            'excerpt' => $article->getExcerpt(),
            'content' => $article->getContent(),
            'sku' => $article->getSku(),
            'price' => $article->getPrice(),
            'image' => $article->getImage(),
            'quantity' => $quantity,
        ]);

        $response = $this->render('/pages/main-clean.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $htmlTemplate,
        ]);

        $response->headers->set('Content-Type', 'text/html');

        return $response;
    }
    
    
    public function viewArticle(Request $request, $id): Response
    {
        /*
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
        */
        
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $article = $articleRepository->findOneBy(['id' => $id]);
		
		if (!$article) {
			throw new NotFoundHttpException('The article was not found.');
		}
        
        $htmlTemplate = $this->renderView('/pages/article.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'id' => $id,
            'title' => $article->getTitle(),
            'excerpt' => $article->getExcerpt(),
            'content' => $article->getContent(),
        ]);

        $response = $this->render('/pages/main.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $htmlTemplate,
        ]);

        $response->headers->set('Content-Type', 'text/html');

        return $response;
    }

    public function viewArticleFetch(Request $request, $id): Response
    {
        $htmlTemplate = $this->renderView('/pages/article-fetch.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'id' => $id
        ]);

        $response = $this->render('/pages/main.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $htmlTemplate,
        ]);

        $response->headers->set('Content-Type', 'text/html');

        return $response;
    }


    public function demoProduct(Request $request): Response
    {
        $htmlTemplate = $this->renderView('/pages/product-demo.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
        ]);
       
        return $this->render('/pages/main-clean.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $htmlTemplate,
        ]);
    }


    public function demoProductList(Request $request): Response
    {
        $htmlTemplate = $this->renderView('/pages/product-list-demo.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
        ]);
       
        return $this->render('/pages/main-clean.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $htmlTemplate,
        ]);
    }


    public function demoProductCheckout(Request $request): Response
    {
        $htmlTemplate = $this->renderView('/pages/product-checkout-demo.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
        ]);
       
        return $this->render('/pages/main-clean.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $htmlTemplate,
        ]);
    }



    public function demoShoppingCart(Request $request): Response
    {
        $component = $this->renderView('/store/components/shopping-cart.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
        ]);        
        
        $htmlTemplate = $this->renderView('/pages/product-shopping-cart-demo.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'component' => $component,
        ]);
       
        return $this->render('/pages/main-clean.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $htmlTemplate,
        ]);
    }



    public function about(Request $request): Response
    {
        $htmlTemplate = $this->renderView('/pages/about.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
        ]);
       
        return $this->render('/pages/main.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $htmlTemplate,
        ]);
    }


    public function checkout(Request $request): Response
    {
        $component = $this->renderView('/store/components/shopping-cart.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
        ]);    
        
        $htmlTemplate = $this->renderView('/pages/product-checkout.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'components' => $component,
        ]);
       
        return $this->render('/pages/main-clean.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $htmlTemplate,            
        ]);
    }



/*
    public function home(Request $request): Response
    {
        return $this->render('/pages/home.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'controller_name' => 'DashboardController',
        ]);
    }
*/

    public function productList(Request $request): Response
    {
        $component = $this->renderView('/store/components/shopping-cart.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
        ]);    
        
        $htmlTemplate = $this->renderView('/pages/product-list.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'components' => $component,
        ]);
       
        return $this->render('/pages/main-clean.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $htmlTemplate,            
        ]);
    }


	

};
