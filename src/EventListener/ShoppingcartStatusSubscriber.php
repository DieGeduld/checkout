<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ShoppingcartStatusSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 10]
            ],
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        if ($routeName === '_wdt') {
            return;
        }

        var_dump($routeName);
    
        // Beispiellogik, um den Status basierend auf dem Routennamen zu ändern
        if ($routeName === 'shopping_route') {
            // Ändere den Status zu 'shopping'
        } elseif ($routeName === 'checkout_route') {
            // Ändere den Status zu 'checkout'
        } else {
            
        }
    }
}
