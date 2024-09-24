<?php

namespace App\Service;

class ShortCodeRegistrar
{
    private $shortCodeService;

    public function __construct(ShortCodeService $shortCodeService)
    {
        $this->shortCodeService = $shortCodeService;
    }

    public function registerShortcodes()
    {        
        // Register all shortcodes here
        $this->shortCodeService->registerShortcode('example', function() {
            return '<p>This is an example shortcode replacement!</p>';
        });

        // You can add more shortcodes here in the future
        // $this->shortCodeService->registerShortcode('another_shortcode', function() {
        //     return '<p>Another shortcode replacement!</p>';
        // });
    }
    
    public function getShortCodeService(): ShortCodeService
    {
        return $this->shortCodeService;
    }
    
};
