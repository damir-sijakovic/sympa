<?php

namespace App\Service;

class HtmlService
{
    private $scripts = [];
    private $styles = [];
    private $lang = 'en';
    private $meta = [];
    private $title = '';
    private $favicon = '';
    private $vars = [];

    public function addScript($script) {
        $this->scripts[] = $script;
    }

    public function addStyle($style) {
        $this->styles[] = $style;
    }

    public function setLang($lang) {
        $this->lang = $lang;
    }

    public function setMeta(array $meta) {
        $this->meta[] = $meta;
    }

    public function setFavicon(string $favicon) {
        $this->favicon = $favicon;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setVar($key, $value) {
		if (is_int($value) || is_float($value)) 
		{
			$this->vars[] = $key . ": " . $value;
		} 
		else
		{
			$this->vars[] = $key . ": '" . $value ."'";
		}
       
       // $this->vars[] = ;
    }

    private function compileHeadElements() {
        $head = "  <head>\n";
        
        error_log( print_r($this->vars, true)  );     
        
        for ($i=0; $i<count($this->meta); $i++){
			$metaTag = '    <meta';
			foreach ($this->meta[$i] as $key => $value) {
                $metaTag .= " {$key}=\"{$value}\"";
            }	
			//error_log( print_r($this->meta[ $i ], true)  );
			$metaTag .= ">\n";
            $head .= $metaTag;
		}
		
        $head .= "    <title>{$this->title}</title>\n";
        
        foreach ($this->styles as $style) {
            $head .= "{$style}\n";
        }
        
        foreach ($this->scripts as $script) {
            $head .= "{$script}\n";
        }
 
		$head .= '<link rel="icon" type="image/png" href="'. $this->favicon .'">'."\n";  
 
		$vars = '<script>window.vars={';
        for ($i=0; $i<count($this->vars); $i++)
        {			
			$vars .= $this->vars[$i] . ",\n";			
		}  
		$vars .= '}</script>';
		       
        $head .= $vars;    
                  
        $head .= "  </head>\n";        

        return $head;
    }

    public function compileHead() {
        $head = "<!DOCTYPE html>\n";
        $head .= "<html lang=\"{$this->lang}\">\n";
        $head .= $this->compileHeadElements();
        $head .= "</html>";
        
        return $head;
    }

    public function compileBody($body = "") {
        $html = "<!DOCTYPE html>\n";
        $html .= "<html lang=\"{$this->lang}\">\n";
        $html .= $this->compileHeadElements();
        $html .= "<body>\n";
        $html .= $body;
        $html .= "\n</body>\n</html>";
        
        return $html;
    }
}




/*

<?php

namespace App\Service;

class HtmlService
{	
    private $scripts = [];
    private $styles = [];
    private $lang = 'en';
    private $meta = [];
    private $title = '';

    public function addScript($script) {
        $this->scripts[] = $script;
    }

    public function addStyle($style) {
        $this->styles[] = $style;
    }

    public function setLang($lang) {
        $this->lang = $lang;
    }

    public function setMeta($key, $value) {
        $this->meta[$key] = $value;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function compileHead() {
        $head = "<!DOCTYPE html>\n";
        $head .= "<html lang=\"{$this->lang}\">\n";
        $head .= "  <head>\n";
        
        foreach ($this->meta as $key => $value) {
            $head .= "    <meta {$key}=\"{$value}\">\n";
        }
        
        $head .= "    <title>{$this->title}</title>\n";
        
        foreach ($this->styles as $style) {
            $head .= "    {$style}\n";
        }
        
        foreach ($this->scripts as $script) {
            $head .= "    {$script}\n";
        }
        
        $head .= "  </head>\n";
      //  $head .= "</html>";
        
        return $head;
    }
    
    public function compileBody($body="") {
        $head = "<!DOCTYPE html>\n";
        $head .= "<html lang=\"{$this->lang}\">\n";
        $head .= "  <head>\n";
        
        foreach ($this->meta as $key => $value) {
            $head .= "    <meta {$key}=\"{$value}\">\n";
        }
        
        $head .= "    <title>{$this->title}</title>\n";
        
        foreach ($this->styles as $style) {
            $head .= "    {$style}\n";
        }
        
        foreach ($this->scripts as $script) {
            $head .= "    {$script}\n";
        }
        
        $head .= "  </head>\n";
        $head .= "<body>";
        $head .= $body;
        $head .= "</body>\n</html>";
        
        return $head;
    }
    

    
}

*/



/*
// Example usage:
$builder = new HtmlHeadBuilder();
$builder->setLang('en');
$builder->setMeta('charset', 'UTF-8');
$builder->setMeta('name', 'viewport');
$builder->setMeta('content', 'width=device-width, initial-scale=1.0');
$builder->setMeta('http-equiv', 'X-UA-Compatible');
$builder->setMeta('content', 'ie=edge');
$builder->setTitle('HTML 5 Boilerplate');
$builder->addStyle('<link rel="stylesheet" href="style.css">');
$builder->addScript('<script src="script.js"></script>');

echo $builder->compile();
*/


    /*
    public function compile() {
        $head = "<!DOCTYPE html>\n";
        $head .= "<html lang=\"{$this->lang}\">\n";
        $head .= "  <head>\n";
        
        foreach ($this->meta as $key => $value) {
            $head .= "    <meta {$key}=\"{$value}\">\n";
        }
        
        $head .= "    <title>{$this->title}</title>\n";
        
        foreach ($this->styles as $style) {
            $head .= "    {$style}\n";
        }
        
        foreach ($this->scripts as $script) {
            $head .= "    {$script}\n";
        }
        
        $head .= "  </head>\n";
      //  $head .= "</html>";
        
        return $head;
    }
    */
