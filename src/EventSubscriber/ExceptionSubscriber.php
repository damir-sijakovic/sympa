<?php

// src/EventSubscriber/ExceptionSubscriber.php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [
                ['processException', 10]
            ],
        ];
    }
    
    
    

    public function processException(ExceptionEvent $event)
    {
        $request = $event->getRequest();
        
        // Check if the request method is not GET
        if ($request->getMethod() !== 'GET') {
            $exception = $event->getThrowable();
            $response = new JsonResponse(
                [
					'error' => $exception->getMessage(),
					'code' => $exception->getCode(),
					'file' => $exception->getFile(),
					'line' => $exception->getLine(),
					'trace' => $exception->getTraceAsString(),
				],
                
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );

            $event->setResponse($response);
        }
        // If it's a GET request, do nothing and let Symfony handle it
    }
    
    
    /*
    public function processException(ExceptionEvent $event)
    {
        $request = $event->getRequest();
        
        // Check if the request method is not GET
        if ($request->getMethod() !== 'GET') {
            $exception = $event->getThrowable();
            $response = new JsonResponse(
                ['error' => $exception->getMessage()],
                
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );

            $event->setResponse($response);
        }
        // If it's a GET request, do nothing and let Symfony handle it
    }
    */
    
    
    
};

/*

      'error' => [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            // Including file and line can be helpful for debugging but be cautious about exposing them in a production environment.
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            // 'trace' => $exception->getTraceAsString(), // Include this only if necessary and be mindful of security and privacy.
        ]

*/



