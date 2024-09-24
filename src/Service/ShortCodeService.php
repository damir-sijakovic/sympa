<?php

namespace App\Service;

class ShortCodeService
{
    private $shortcodes = [];

    // Register a shortcode with its callback
    public function registerShortcode(string $shortcode, callable $callback)
    {
        $this->shortcodes[$shortcode] = $callback;
    }


    public function parseShortcodes(string $content): string
    {
        /*
        $pattern = '/\[([a-zA-Z0-9_]+)\]/';
        
        $callback = function ($matches) {
            $shortcode = $matches[1];
            if (isset($this->shortcodes[$shortcode])) {
                return call_user_func($this->shortcodes[$shortcode]);
            }

            return $matches[0];
        };

        $newContent = preg_replace_callback($pattern, $callback, $content);
                
        error_log(":::::::: " . $newContent);
        
        // Return the modified content
        return $newContent;
        */
        
        /*        
        $t = $content;

        foreach($this->shortcodes as $k => $v)
        {		
            if (is_string($v))
            {
                $t = str_replace('[['. $k .']]', $v, $t);
            }
        }	
        */
        
        
        $pattern = '/\[\[([a-zA-Z0-9_]+)\]\]/';
        $tokens = [];
        preg_match_all($pattern, $content, $matches);

        if (isset($matches[1])) {
            $tokens = $matches[1];
        }

        error_log("shortcodes " .print_r(  $this->shortcodes ,true));   
        
        $callbackData = null;
        $newContent = $content;   
        for ($i=0; $i<count($tokens); $i++)
        {                   
            $shortcode = $tokens[$i];
            if (isset($this->shortcodes[$shortcode])) {
                $callbackData = call_user_func($this->shortcodes[$shortcode]);
                $newContent = str_replace('[['. $shortcode .']]', $callbackData, $newContent);
            }  
        }

        
        return $newContent;        
    }


/*
    // Parse the content and replace shortcodes with the corresponding HTML
    public function parseShortcodes(string $content): string
    {
        return preg_replace_callback('/\[([a-zA-Z0-9_]+)\]/', function ($matches) {
            $shortcode = $matches[1];
            if (isset($this->shortcodes[$shortcode])) {
                return call_user_func($this->shortcodes[$shortcode]);
            }
            return $matches[0]; // return the original shortcode if no match
        }, $content);
    }
*/ 
    
    
}
