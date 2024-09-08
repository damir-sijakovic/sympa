<?php

namespace App\Controller;

use App\Entity\User;
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
    
    public function viewArticle(Request $request, $id): Response
    {
        $htmlTemplate = $this->renderView('/pages/article.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'id' => $id
        ]);
       
        return $this->render('/pages/main.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
            'body' => $htmlTemplate,
        ]);
    }


    public function about(Request $request): Response
    {
        $htmlTemplate = $this->renderView('/pages/artcle.html.twig', [
            'rootUrl' => $this->utilityHelper->getRootUrl(),
        ]);
       
        return $this->render('/pages/main.html.twig', [
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


	

};
