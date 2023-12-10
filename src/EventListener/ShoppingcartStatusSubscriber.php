<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Product;
use App\Entity\ShoppingCart;
use App\Entity\ShoppingCartProduct;
use App\Service\ProductService;
use Twig\Environment as TwigEnvironment;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\Workflow\Event\WorkflowEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
//TokenStorage

class ShoppingcartStatusSubscriber implements EventSubscriberInterface
{
    private $security;
    private $entityManager;
    private $twig;
    private $router;
    private $workflow;
    private $tokenStorage;
    


    public function __construct(Security $security, EntityManagerInterface $entityManager, TwigEnvironment $twig, TokenStorageInterface $tokenStorage)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->twig = $twig;
        $this->tokenStorage = $tokenStorage;

    }


    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 5]
            ],
            // 'workflow.checkout_process.to_shopping_cart' => 'onTransitionToShoppingCart',
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

        $accessToken = $this->tokenStorage->getToken();

        // get current user

        // immer den aktuellen Warenkorb mitgeben:
        if ($accessToken === null) {
            
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

            var_dump("Nicht eingelogt: " . $shoppingCart->getId());

        } else {

            $user = $accessToken->getUser();

            // Wenn der User eingeloggt ist
            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['user_id' => $this->security->getUser()->getId() ]);

            // if shopping cart does not exist, create new one
            if ($shoppingCart === null) {
                $shoppingCart = new ShoppingCart();
                $shoppingCart->setUserId($user);
                $shoppingCart->setState('shopping5');
                $this->entityManager->persist($shoppingCart);
                $this->entityManager->flush();
                var_dump("Neuer Warenkorb: " . $shoppingCart->getId());
            }
            var_dump("Eingelogt: " . $shoppingCart->getId());
        }  
  

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select([
            'p.id AS id', 
            'scp.quantity AS quantity', 
            'p.name AS name', 
            'p.price AS price', 
            'p.price AS product_price',
            'p.price * scp.quantity AS sum'
        ])
        ->from(ShoppingCartProduct::class, 'scp')
        ->where('scp.shoppingcart = :shoppingcart')
        ->join('scp.product', 'p')
        ->setParameter('shoppingcart', $shoppingCart->getId());
    
        $products = $queryBuilder->getQuery()->getResult();

        /////

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select([
            'SUM(p.price * scp.quantity) AS sum'
        ])
        ->from(ShoppingCartProduct::class, 'scp')
        ->where('scp.shoppingcart = :shoppingcart')
        ->join('scp.product', 'p')
        ->setParameter('shoppingcart', $shoppingCart->getId());

        $sum = $queryBuilder->getQuery()->getResult();

        $this->twig->addGlobal('shoppingcart', $products);        
        $this->twig->addGlobal('shoppingcartsum', $sum[0]["sum"]);        
        
    }

    public function onTransitionToShoppingCart(RequestEvent $requestEvent)
    {
        dd("!!");

    }

}
