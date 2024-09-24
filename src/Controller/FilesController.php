<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use DirectoryIterator;
use App\Helper\UtilityHelper;

class FilesController extends AbstractController
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
        $this->uploadUrl = $this->utilityHelper->getRootUrl() . '/uploads/files';
        $this->uploadDir = $this->utilityHelper->getPublicDir() . '/uploads/files';
	}
    


    public function upload(Request $request): Response
    {
        $allowedExtensions = ['txt', 'zip', 'pdf', 'docx', 'xlsx', 'md', 'csv'];

        // $groupDirName = $this->uploadDir .'/'. $slug;
        $file = $request->files->get('file');
        $groupNameSlug = $request->request->get('slug');
        
        if ($file && $groupNameSlug) 
        {            
            $groupDirName = $this->uploadDir .'/'. $groupNameSlug;
            
            if (!file_exists($groupDirName)) {
                return $this->json([
                    'error' => true, 
                    'message' => 'Group name dnot found.', 
                    'data' => $groupNameSlug 
                ],404);
            } 
            
            $originalExtension = $file->getClientOriginalExtension(); 
            $originalName = $file->getClientOriginalName();
            $originalNameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $newSlug = strtolower($this->slugger->slug($originalNameWithoutExtension)->toString());
            $fileSize = $file->getSize();
            $humanFileSize = $this->utilityHelper->humanFileSize($file->getSize());
            $fileName = "file." . $extension;
            
            if (!in_array($extension, $allowedExtensions)) {
                return $this->json([
                    'error' => true, 
                    'message' => 'Bad extension.', 
                    'data' => null
                ],415);
            } 
              
            $jsonData = json_encode([
                "name" => $originalNameWithoutExtension,
                "slug" => $newSlug,
                "description" => $originalName,  
                "extension" => $extension,   
                "size" => $fileSize,  
                "humanFileSize"  => $humanFileSize,         
                "fileName"  => $fileName,        
            ]);
                
            $uuid = $this->utilityHelper->generateUuid();   
                           
            //$imageDirName = $groupDirName .'/'. $newSlug .'-'. $uuid;
            $imageDirName = $groupDirName .'/'. $uuid;
            $imageJsonFile = $imageDirName . '/info.json';
            
            if (mkdir($imageDirName, 0755, true))
            {            
                if (file_put_contents($imageJsonFile, $jsonData) === false) 
                {
                    return $this->json([
                        'error' => true, 
                        'message' => "Error creating 'info.json'.", 
                        'data' => null
                    ], 500); 
                }
                
                // $this->convertToWebp($file, $extension, $imageDirName .'/original.webp');
                //$this->utilityHelper->convertToWebp($file, $extension, $imageDirName .'/original.webp');
                //$this->utilityHelper->resizeAndConvertToWebp($file, $imageDirName);
                
                try {
					$file->move(
						$imageDirName, // Make sure you have this parameter set in services.yaml
						'file' .'.'. $extension
					);
				} catch (FileException $e) {
                    return $this->json([
                        'error' => true, 
                        'message' => 'Error uploading fiel.', 
                        'data' => null
                    ], 500);  
				}
                
                return $this->json([
                    'error' => false, 
                    'message' => 'File uploaded.', 
                    'data' => null
                ]);
            } 
            else {
                return $this->json([
                    'error' => true, 
                    'message' => 'Error creating directory.', 
                    'data' => null
                ], 500);  
            }
                
            return $this->json(['ok' => $imageDirName]);           
        }
        else
        {
            return $this->json([
                'error' => true, 
                'message' => 'Missing request params.', 
                'data' => null
            ],400);
        }            

        return $this->json(['status' => $slug]);
    }



    public function createGroupPost(Request $request): Response
    {     
		$name = $request->request->get('name');
		$description = $request->request->get('description', '');
		$slug = strtolower($this->slugger->slug($name)->toString());

        if (empty($name) || strlen($name)<3 ) {
			return $this->json([
                'error' => true, 
                'message' => 'Title too short.', 
                'data' => null
            ],400);
		}
                
        $groupDirName = $this->uploadDir .'/'. $slug;
        $jsonInfoFile = $groupDirName .'/info.json';               
                
        if (file_exists($groupDirName)){
            return $this->json([
                'error' => true, 
                'message' => 'Group name already exists.', 
                'data' => null
            ], 409);            
        }
        
        $jsonData = '
            {
                "name":"'. $name .'",
                "description": "'. $description .'"
            }        
        ';
                
        if (mkdir($groupDirName, 0755, true))
        {            
            if (file_put_contents($jsonInfoFile, $jsonData) === false) 
            {
                return $this->json([
                    'error' => true, 
                    'message' => "Error creating 'info.json'.", 
                    'data' => null
                ], 500); 
            }
            
            return $this->json([
                'error' => false, 
                'message' => 'Group created.', 
                'data' => null
            ]);
        } 
        else {
            return $this->json([
                'error' => true, 
                'message' => 'Error creating directory.', 
                'data' => null
            ], 500);  
        }
        

    }



    public function getGroups(Request $request): Response
    {
        try {
			$uploadDirectory = $this->uploadDir;
			$uploadUrl = $this->uploadUrl;
            
			$groups = [];	
            $count = 0;    
			foreach (new \DirectoryIterator($uploadDirectory) as $file) {
				if ($file->isDot()) continue;
				if ($file->isDir()) {
					$fileName = $file->getFilename();
                    
                    $jsonInfoPath = $uploadDirectory . '/' . $fileName . '/info.json';
                    $jsonInfoUrl = $uploadUrl . '/' . $fileName . '/info.json';
                    
                    if (!file_exists($jsonInfoPath)){
                        return $this->json([
                            'data' => null,
                            'error' => true,
                            'message' => 'Invalid group data!'
                        ]);
                    }
                    
                    $jsonData = file_get_contents($jsonInfoPath);
                    $data = json_decode($jsonData, true);    
                    
                    if (!array_key_exists('name', $data)) {
                        return $this->json([
                            'data' => null,
                            'error' => true,
                            'message' => 'Invalid group data!'
                        ]);
                    } 
                    
                    if (!array_key_exists('description', $data)) {
                        return $this->json([
                            'data' => null,
                            'error' => true,
                            'message' => 'Invalid group data!'
                        ]);
                    }                     
    
					$count++;
                    			                
					$groups[] = [
                        'dir' => $fileName,
                        'infoFile' => $jsonInfoUrl,
                        'createdAt' => $file->getCTime(),   
                        'name' => $data['name'],    
                        'description' => $data['description'],                        
                    ];
				}                
			}
            
            usort($groups, function ($a, $b) {
                return $b['createdAt'] <=> $a['createdAt'];
            }); 

			return $this->json([
                'data' => ['groups' => $groups, 'total' => $count],
                'error' => false,
                'message' => ''
            ]);
		} catch (\Exception $e) {
			return $this->json([
                    'error' => true,
                    'data' => null,
                    'message' => $e->getMessage(),                
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
    }









 public function getGroupFiles(Request $request, $slug): Response
    {        
        $imageDirectory = $this->uploadUrl .'/'. $slug;        
        $uploadDirectory = $this->uploadDir .'/'. $slug;
        if (!file_exists($uploadDirectory)){
            return $this->json([
                'data' => null,
                'error' => true,
                'message' => 'Group data not found!'
            ]);
        }
        
        try {
			
			$files = [];	
            $count = 0;    
			foreach (new \DirectoryIterator($uploadDirectory) as $file) {
				if ($file->isDot()) continue;
				if ($file->isDir()) {
                    
					$fileDirName = $file->getFilename();
                    
                    $jsonInfoPath = $uploadDirectory . '/' . $fileDirName . '/info.json';
                    
                    if (!file_exists($jsonInfoPath)){
                        return $this->json([
                            'data' => null,
                            'error' => true,
                            'message' => 'Invalid group data!'
                        ]);
                    }
                    
                    $jsonData = file_get_contents($jsonInfoPath);
                    $data = json_decode($jsonData, true);    
                    
                    if (!array_key_exists('name', $data)) {
                        return $this->json([
                            'data' => null,
                            'error' => true,
                            'message' => 'Name missing, invalid group data!'
                        ]);
                    } 
                    
                    if (!array_key_exists('description', $data)) {
                        return $this->json([
                            'data' => null,
                            'error' => true,
                            'message' => 'Description missing, invalid group data!'
                        ]);
                    } 
                    
                    if (!array_key_exists('slug', $data)) {
                        return $this->json([
                            'data' => null,
                            'error' => true,
                            'message' => 'Slug missing, invalid group data!'
                        ]);
                    } 
                         
					$count++;
                              
					$files[] = [
                        'group' => $slug,
                        'dir' => $fileDirName,
                        'url' => $imageDirectory .'/'. $fileDirName,
                        'name' => $data['name'],
                        'slug' => $data['slug'],
                        'description' => $data['description'],
                        'fileName' => $data['fileName'],
                        'download' => $imageDirectory .'/'. $fileDirName .'/'. $data['fileName'],
                    ];
				}

			}
            
			return $this->json([
                'data' => ['files' => $files, 'count' => $count],
                'error' => false,
                'message' => ''
            ]);
            
		} catch (\Exception $e) {
			return $this->json([
                    'error' => true,
                    'data' => null,
                    'message' => $e->getMessage(),                
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
                
    }








};
