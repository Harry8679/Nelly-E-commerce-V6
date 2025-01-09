<?php

namespace App\EventSubscriber;

use App\Classe\Cart;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class TwigCartSubscriber implements EventSubscriberInterface
{
    private $cart;
    private $twig;

    public function __construct(Cart $cart, Environment $twig)
    {
        $this->cart = $cart;
        $this->twig = $twig;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $this->twig->addGlobal('cart', $this->cart->getCart());
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
