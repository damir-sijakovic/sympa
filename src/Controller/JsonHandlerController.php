<?php

namespace App\Controller;

use App\Entity\ShopOrders;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use DirectoryIterator;
use App\Helper\UtilityHelper;

class JsonHandlerController extends AbstractController
{
    private $entityManager;
    private $utilityHelper;
    private $uploadUrl;
    private $uploadDir;
    private $slugger;
    
	public function __construct(
        EntityManagerInterface $entityManager, 
        UtilityHelper $utilityHelper,
        SluggerInterface $slugger
    )
    {
        $this->slugger = $slugger;
        $this->entityManager = $entityManager;	
        $this->utilityHelper = $utilityHelper;	
        $this->uploadUrl = $this->utilityHelper->getRootUrl() . '/uploads/jsons';
        $this->uploadDir = $this->utilityHelper->getPublicDir() . '/uploads/jsons';
	}
    
       

    public function index(Request $request): Response
    {      
        return $this->json(['ok' => 234234]);
    }


    public function saveTreeData(Request $request): Response
    {
        $treeData = $request->request->get('treeData');
        
        error_log( print_r( $treeData , true) );
        
        $filePath = $this->uploadDir . '/filePath.json';

        if (file_put_contents($filePath, $treeData) === false) {
            return $this->json(['error' => "Error writing to file!"]);
        }
                
        return $this->json(['ok' => 234234]);
    }


    public function loadTreeData(Request $request): Response
    {
        $filePath = $this->uploadDir . '/filePath.json';
    
        if (!file_exists($filePath)){
            return $this->json(['error' => "File not found!"]);
        }

        if (file_put_contents($filePath, $treeData) === false) {
            return $this->json(['error' => "Error writing to file!"]);
        }
                
        return $this->json(['ok' => 234234]);
    }





};
