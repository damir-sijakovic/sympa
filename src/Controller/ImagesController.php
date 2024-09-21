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

class ImagesController extends AbstractController
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
        $this->uploadUrl = $this->utilityHelper->getRootUrl() . '/uploads/images';
        $this->uploadDir = $this->utilityHelper->getPublicDir() . '/uploads/images';
	}
    



    public function upload(Request $request): Response
    {
        $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'webp'];
        
        // $groupDirName = $this->uploadDir .'/'. $slug;
        $file = $request->files->get('file');
        $groupNameSlug = $request->request->get('slug');
        
        if ($file && $groupNameSlug) 
        {            
            $groupDirName = $this->uploadDir .'/'. $groupNameSlug;
            
            if (!file_exists($groupDirName)) {
                return $this->json([
                    'error' => true, 
                    'message' => 'Group name not found.', 
                    'data' => null
                ],404);
            } 
            
            $originalExtension = $file->getClientOriginalExtension(); 
            $originalName = $file->getClientOriginalName();
            $originalNameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $newSlug = strtolower($this->slugger->slug($originalNameWithoutExtension)->toString());

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
            ]);
                           
            $imageDirName = $groupDirName .'/'. $newSlug .'-'. time();
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
                $this->convertToWebp($file, $extension, $imageDirName .'/original.webp');
                $this->resizeAndConvertToWebp($file, $imageDirName);
                
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
            
            /*
            if (file_exists($imageDirName)){
                return $this->json([
                    'error' => true, 
                    'message' => 'File name already exists.', 
                    'data' => null
                ],409);
            }
            */
            
          //  $this->convertToWebp($file, $extension, $imageDirName);
            
            
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
            
        /*
        if ($file && $slug) {
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
            $filename = uniqid() . '.' . $file->guessExtension();

            // Move the uploaded file to the uploads directory
            $file->move($uploadDir, $filename);

            return $this->json(['status' => 'success', 'filename' => $filename, 'custom_field' => $customField]);
        }
        */
        
/*
        if ($files && is_array($files)) {
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
                    $filename = uniqid() . '.' . $file->guessExtension();

                    // Move the uploaded file to the uploads directory
                    $file->move($uploadDir, $filename);

                    // Store the filename
                    $uploadedFilenames[] = $filename;
                }
            }
        }
*/

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



    public function getGroupImages(Request $request, $slug): Response
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
			
			$images = [];	
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
                              
					$images[] = [
                        'group' => $slug,
                        'url' => $imageDirectory .'/'. $fileDirName,
                        'name' => $data['name'],
                        'slug' => $data['slug'],
                        'description' => $data['description'],
                    ];
				}

			}
            
			return $this->json([
                'data' => ['images' => $images, 'count' => $count],
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
                        'description' => $data['description']
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
    
    
    
    public function updateGroupInfoPost(Request $request): Response
    {      
        $name = $request->request->get('name');
		$description = $request->request->get('description', '');
		$group = $request->request->get('group');
        
        $uploadDirectory = $this->uploadDir;
        $jsonFile = $uploadDirectory .'/'. $group .'/info.json';
        
        if (!file_exists($jsonFile))
        {
            return $this->json([
                'error' => true,
                'message' => 'Json file not found!',
                'data' => null,
            ], 404);
        }
        
        $jsonData = json_encode([
            'name' => $name,
            'description' => $description,
        ]);
        
        if (file_put_contents($jsonFile, $jsonData) === false) 
        {
            return $this->json([
                'error' => true, 
                'message' => "Error creating 'info.json'!", 
                'data' => null,
            ], 500); 
        }
        
        
        return $this->json([
            'error' => false,
            'message' => 'Info file updated!',
            'data' => null,
        ], 200); 
    }
	
    
    
    
    //PRIVATE
    private function resizeAndConvertToWebp(UploadedFile $file, string $fileDir)
    {
        $extension = strtolower($file->getClientOriginalExtension());

        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                $image = imagecreatefromjpeg($file->getPathname());
                break;
            case 'png':
                $image = imagecreatefrompng($file->getPathname());
                break;
            case 'gif':
                $image = imagecreatefromgif($file->getPathname());
                break;
            default:
                throw new \Exception('Unsupported image type');
        }

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        $sizes = [
            ['width' => 200, 'height' => 200],
            ['width' => 400, 'height' => 400]
        ];

        foreach ($sizes as $size) {
            $resizedImage = $this->resizeImage($image, $originalWidth, $originalHeight, $size['width'], $size['height']);
            //$webpFilePath = $fileDir . '/' . uniqid() . '_' . $size['width'] . 'x' . $size['height'] . '.webp';
            //$webpFilePath = $fileDir . '/' . $size['width'] . 'x' . $size['height'] . '.webp';
            $webpFilePath = $fileDir . '/' . $size['width'] . '.webp';
            $this->convertToWebpFromResource($resizedImage, $webpFilePath);
            imagedestroy($resizedImage);
        }

        imagedestroy($image);
    }
    
    
    private function convertToWebp(UploadedFile $file, string $extension, string $webpFilePath)
    {
        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                $image = imagecreatefromjpeg($file->getPathname());
                break;
            case 'png':
                $image = imagecreatefrompng($file->getPathname());
                break;
            case 'gif':
                $image = imagecreatefromgif($file->getPathname());
                break;
            default:
                throw new \Exception('Unsupported image type');
        }

        // Save the image in WEBP format
        if ($image !== false) {
            imagewebp($image, $webpFilePath, 80); // 80 is the quality (0-100)
            imagedestroy($image); // Free up memory
        } else {
            throw new \Exception('Image conversion failed');
        }
    }
    
    //Resize an image while preserving the aspect ratio.
    private function resizeImage($image, int $originalWidth, int $originalHeight, int $targetWidth, int $targetHeight)
    {
        // Calculate aspect ratio
        $aspectRatio = $originalWidth / $originalHeight;

        // Determine new dimensions while preserving the aspect ratio
        if ($originalWidth > $originalHeight) {
            $newWidth = $targetWidth;
            $newHeight = $targetWidth / $aspectRatio;
        } else {
            $newHeight = $targetHeight;
            $newWidth = $targetHeight * $aspectRatio;
        }

        // Create a new true color image with the new dimensions
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Copy and resize the original image into the resized image
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        return $resizedImage;
    }


    //Convert a GD image resource to WEBP format.
    private function convertToWebpFromResource($image, string $webpFilePath)
    {
        if ($image !== false) {
            imagewebp($image, $webpFilePath, 80); // Save as WEBP with 80 quality
        } else {
            throw new \Exception('Image conversion failed');
        }
    }
    

};
