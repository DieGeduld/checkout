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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Services\ProductService;

class ShoppingcartStatusSubscriber implements EventSubscriberInterface
{
    private $security;
    private $twig;
    private $requestStack;
    private $session;
    private $sessionId;
    private $productService;
    private $shoppingCartProducts;
    private $shoppingCart;
    
    public function __construct(Security $security, EntityManagerInterface $entityManager, TwigEnvironment $twig, RequestStack $requestStack, ProductService $productService)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->twig = $twig;
        $this->requestStack = $requestStack;
        $this->productService = $productService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 10]
            ],
            // 'workflow.checkout_process.to_shopping_cart' => 'onTransitionToShoppingCart',
            // 'workflow.checkout_process.to_shopping_cart' => ['onTransitionToShoppingCart'],
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {

        $this->session = $this->requestStack->getSession();
        $this->sessionId = $this->session->getId();

        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        if ($routeName === '_wdt') {
            return;
        }
        
        $this->shoppingCartProducts = $this->productService->getShoppingCartProducts();
        $this->shoppingCart = $this->productService->getShoppingCart();

        $this->twig->addGlobal('shoppingcart', $this->shoppingCartProducts);        
        $this->twig->addGlobal('shoppingcartsum', $this->productService->getShoppingCartSum());        
        
    }

    public function onTransitionToShoppingCart()
    {
        // dd("!yyyy!");
    }

}
