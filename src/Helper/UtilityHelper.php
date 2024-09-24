<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UtilityHelper
{
    const PROJECT_DIR = __DIR__ . '/../..';
    
	private $session;
	
	function generateRandomString($length = 16) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
    
    function generateUuid() 
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set variant to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    

    function paginate($itemNumber, $perPage, $currentPage, $visiblePages = 3)
    {
        // Check if itemNumber is a positive integer; return null if not.
        if (!is_int($itemNumber) || $itemNumber <= 0) {
            return null;
        }

        // Ensure perPage is at least 1 and does not exceed itemNumber.
        $perPage = max(1, min($perPage, $itemNumber));

        // Calculate the total number of pages.
        $numberOfPages = ceil($itemNumber / $perPage);

        // Adjust currentPage to be within the range of available pages.
        $currentPage = max(1, min($currentPage, $numberOfPages));

        // Calculate the index of the first item on the current page for database queries.
        $limitFirst = ($currentPage - 1) * $perPage;

        // Determine the number of items to show on the current page.
        $indexRowCount = min($perPage, $itemNumber - $limitFirst);

        // Calculate the start and end page numbers to be shown in the pagination bar.
        $visibleStartPage = max(1, $currentPage - floor($visiblePages / 2));
        $visibleEndPage = min($numberOfPages, $visibleStartPage + $visiblePages - 1);

        // Adjust the start page if the calculated range is smaller than the desired range of visible pages.
        if ($visibleEndPage - $visibleStartPage < $visiblePages - 1) {
            $visibleStartPage = max(1, $visibleEndPage - $visiblePages + 1);
        }

        // Construct and return an associative array with the pagination details.
        return [
            'itemNumber' => $itemNumber,
            'perPage' => $perPage,
            'currentPage' => $currentPage,
            'numberOfPages' => $numberOfPages,
            'indexOffset' => $limitFirst, // The offset for SQL queries.
            'indexRowCount' => $indexRowCount, // Number of rows to retrieve in the query.
            'visibleStartPage' => $visibleStartPage,
            'visibleEndPage' => $visibleEndPage,
            'firstPage' => 1, // Always 1.
            'lastPage' => $numberOfPages, // The total number of pages.
            'prevPage' => $currentPage > 1 ? $currentPage - 1 : null, // Previous page number or null.
            'nextPage' => $currentPage < $numberOfPages ? $currentPage + 1 : null, // Next page number or null.
        ];
    }
    
    
    function replaceSpecialCharacters($string) 
    {		
		$search = ['<', '>', '"', "'", '/', '\\'];
		$replace = ['‹', '›', '”', '’', '⁄', '∖'];
		return str_replace($search, $replace, $string);
	}


	function reverseReplaceSpecialCharacters($string) 
	{
		$replace = ['<', '>', '"', "'", '/', '\\']; 
		$search = ['‹', '›', '”', '’', '⁄', '∖']; 
		return str_replace($search, $replace, $string);
	}


	function removeSpecialChars($string) 
	{
		$chars_to_remove = array("'", '"', '\\');
		$clean_string = str_replace($chars_to_remove, '', $string);		
		return $clean_string;
	}
	
	
	function escapeHtml($string) {
		return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}


	function unescapeHtml($string) {
		return html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}
	
	function truncateText($text, $maxLength) {
		if (strlen($text) > $maxLength) {
			$truncatedText = substr($text, 0, $maxLength - 3);
			$truncatedText .= '...';
			return $truncatedText;
		}

		return $text;
	}
	
	function removeNewLines($text) {
		return str_replace(["\r\n", "\r", "\n"], '', $text);
	}
	
	function replaceNewLinesWithBr($text) {
		return str_replace(["\r\n", "\r", "\n"], '<br>', $text);
	}
	
	function isSlug(string $string): bool {
		return preg_match('/^[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*$/', $string) === 1;
	}
	
	function isCleanText(string $string): bool {
		return preg_match('/^[a-zA-Z0-9,.\-\+!?đšžćčŽĆČĐŠ ]+$/', $string) === 1;
	}
	
	public function loadJson(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File does not exist: $filePath");
        }

        $jsonContent = file_get_contents($filePath);
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON decode error: " . json_last_error_msg());
        }

        return $data;
    }


    public function saveJson(array $data, string $filePath): void
    {
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT);
        if ($jsonContent === false) {
            throw new \Exception("JSON encode error: " . json_last_error_msg());
        }

        if (file_put_contents($filePath, $jsonContent) === false) {
            throw new \Exception("Failed to write to file: $filePath");
        }
    }
    
    
    
	
    public function dd($value): void
    {
        error_log(print_r($value,true));
    }
	
	
    public function test(): string
    {
        return ' <h1> utility helper </h1> ';
    }
    
    
	function generateSessionTable(SessionInterface $session) {
		$html = '<table border="1" class="table table-sm"><tr><th>Key</th><th>Value</th></tr>';
		foreach ($session->all() as $key => $value) {
			$html .= '<tr><td>' . htmlspecialchars($key) . '</td><td>' . htmlspecialchars($value) . '</td></tr>';
		}
		$html .= '</table>';
		return $html;
	}
    
    function humanFileSize($bytes, $decimals = 2) {
		$size = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
	}
        
    function getRootUrl() 
	{
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$serverName = $_SERVER['SERVER_NAME'];
		$port = $_SERVER['SERVER_PORT'];
		$port = ($port == 80 || $port == 443) ? '' : ':' . $port;

		return $protocol . $serverName . $port;
	}		
	
    function getPublicDir() 
	{
		return self::PROJECT_DIR . '/public';
	}		
	
	function templateString($str, $assoc) 
    {
        if (!is_array($assoc))
        {
            error_log('templateString(): $assoc parametar must be associative array!');
        }

        $t = $str;

        foreach($assoc as $k => $v)
        {		
            if (is_string($v))
            {
                $t = str_replace('{{'. $k .'}}', $v, $t);
            }
        }	
        
        $purgedTemplateData = preg_replace('/{{[\s\S]+?}}/', '', $t);
        return $purgedTemplateData;
    }
    
    
    function resizeAndConvertToWebp(UploadedFile $file, string $fileDir)
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
    
    
    function convertToWebp(UploadedFile $file, string $extension, string $webpFilePath) 
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
    function resizeImage($image, int $originalWidth, int $originalHeight, int $targetWidth, int $targetHeight)
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
    function convertToWebpFromResource($image, string $webpFilePath)
    {
        if ($image !== false) {
            imagewebp($image, $webpFilePath, 80); // Save as WEBP with 80 quality
        } else {
            throw new \Exception('Image conversion failed');
        }
    }
    
    
};
