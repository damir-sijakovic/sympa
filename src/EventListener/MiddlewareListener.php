<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;


class MiddlewareListener
{
    
	//BEFORE
	public function onKernelController(ControllerEvent $event)
    {	
        error_log("BEFORE");
        $request = $event->getRequest();	
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
        

		
		/*
		if (strpos($request->getPathInfo(), '/dashboard') === 0) 
        {	
			if (!$request->hasSession()) 
			{
				//return;
			}

			$session = $request->getSession();
			if ($session->has('userLoggedIn')) 
			{
				//$loggedIn = $session->get('userLoggedIn');
				return new RedirectResponse('/dashboard');
			} 
			else 
			{
				$event->setController(function() {
					return new Response('Access Denied', 403);
				});
			}
        }	
        */	        

    }

	//AFTER
    public function onKernelResponse(ResponseEvent $event)
    {
		error_log("AFTER");
		/*
		$response = $event->getResponse();
		$response->headers->set('X-My-Header', 'my-value');
		$event->setResponse($response); 
		*/
    }
	


};


