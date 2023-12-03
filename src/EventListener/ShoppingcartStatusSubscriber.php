<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Product;
use App\Entity\ShoppingCart;
use App\Entity\ShoppingCartProduct;
use Twig\Environment as TwigEnvironment;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\Workflow\Event\WorkflowEvent;
use Symfony\Bundle\SecurityBundle\Security;

class ShoppingcartStatusSubscriber implements EventSubscriberInterface
{
    private $security;
    private $entityManager;
    private $twig;
    private $router;
    private $workflow;


    public function __construct(Security $security, EntityManagerInterface $entityManager, TwigEnvironment $twig)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->twig = $twig;
    }


    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 10]
            ],
            'workflow.checkout_process.to_shopping_cart' => 'onTransitionToShoppingCart',
            // 'workflow.checkout_process.to_shopping_cart' => ['onTransitionToShoppingCart'],
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
        
        // get current user
        $user = $this->security->getUser();

        

        // immer den aktuellen Warenkorb mitgeben:

        if ($user === null) {
            
            $sessionId = $request->getSession()->getId();

            
            // get shopping cart by session id
            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['sessionId' => $sessionId]);

            // if shopping cart does not exist, create new one
            if ($shoppingCart === null) {
                $shoppingCart = new ShoppingCart();
                $shoppingCart->setSessionId($sessionId);
                $shoppingCart->setState('shopping');
                $this->entityManager->persist($shoppingCart);
                $this->entityManager->flush();
            }
        } else {

            // Wenn der User eingeloggt ist
            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['user_id' => $this->security->getUser()->getId() ]);

            // if shopping cart does not exist, create new one
            if ($shoppingCart === null) {
                $shoppingCart = new ShoppingCart();
                $shoppingCart->setUserId($user);
                $shoppingCart->setState('shopping');
                $this->entityManager->persist($shoppingCart);
                $this->entityManager->flush();
            }
        }  

        
        

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select([
            'p.id AS id', 
            'scp.quantity AS quantity', 
            // 'p.id AS product_id', 
            'p.name AS name', 
            'p.price AS price', 
            'p.price AS product_price'
        ])
        ->from(ShoppingCartProduct::class, 'scp')
        ->join('scp.product', 'p');       
    
        $products = $queryBuilder->getQuery()->getResult();
        $this->twig->addGlobal('shoppingcart', $products);
        
    }

    public function onTransitionToShoppingCart(RequestEvent $requestEvent)
    {
        dd("!!");

    }

}
