<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use App\Service\ShortCodeRegistrar;

class MiddlewareListener
{
    private $shortCodeRegistrar;

    public function __construct(ShortCodeRegistrar $shortCodeRegistrar)
    {
        $this->shortCodeRegistrar = $shortCodeRegistrar;
    }

    // BEFORE
    public function onKernelController(ControllerEvent $event)
    {
        error_log("BEFORE");

        $request = $event->getRequest();
        
        //NONCE
        $request->attributes->set('nonce', bin2hex(random_bytes(16)));
        
        //DASHBOARD
        if (strpos($request->getPathInfo(), '/dashboard') === 0)
        {
            $session = $request->getSession();
            if (!$session->has('userLoggedIn'))
            {
                $event->setController(function() {
                    return new Response('Access Denied', 403);
                });
            }            
        }
        else{
            // Register shortcodes via ShortCodeRegistrar
            $this->shortCodeRegistrar->registerShortcodes();             
        }
        
    }

    // AFTER
    public function onKernelResponse(ResponseEvent $event)
    {
        error_log("AFTER");
        $response = $event->getResponse();
        $request = $event->getRequest(); 
        
        if ($response->headers->get('Content-Type') === 'text/html') 
        {    
            $content = $response->getContent();            
            $newContent = $this->shortCodeRegistrar->getShortCodeService()->parseShortcodes($content); // Parse and replace shortcodes using the getter
            $response->setContent($newContent); // Set the new content back to the response
        }
        
        //XSS
        $nonce = $request->attributes->get('nonce');
        $response->headers->set('X-XSS-Protection', '1; mode=block');   
        //  $response->headers->set('Content-Security-Policy', "script-src 'self' 'nonce-$nonce';");
        /*
            <script nonce="{{ nonce }}">
            // Your inline JavaScript code here
            console.log('This script is allowed because it has the correct nonce.');
            </script>
        */
    }
}
