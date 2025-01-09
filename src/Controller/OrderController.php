<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarDumper\Cloner\Data;

class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/commande", name="app_order")
     */
    public function index(Cart $cart, Request $request): Response
    {
        if (!$cart->getCart()) {
            return $this->redirectToRoute('app_cart');
        }

        $user = $this->getUser();
        $order = new Order();

        $form = $this->createForm(OrderType::class, $order);


        return $this->render('order/order.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getCart()
        ]);
    }

    /**
     * @Route("/commande/recapitulatif", name="app_order_recap")
     */
    public function add(Cart $cart, Request $request): Response
    {
        if (!$cart->getCart()) {
            return $this->redirectToRoute('app_cart');
        }

        $user = $this->getUser();
        $order = new Order();

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $date = new DateTime();
            $order->setUser($user);
            $order->setCreatedAt($date);
            $order->setIsPaid(0);

            $this->entityManager->persist($order);
            // dd($form->getData());

            foreach ($cart->getCart() as $product) {
                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order)
                    ->setProduct($product['product']->getName())
                    ->setIllustration($product['product']->getIllustration())
                    ->setQuantity($product['quantity'])
                    ->setPrice($product['product']->getPrice())
                    ->setTotal($product['product']->getPrice() * $product['quantity']);
                $this->entityManager->persist($orderDetails);
            }

            $this->entityManager->flush();
        }


        return $this->render('order/add.html.twig', [
            'cart' => $cart->getCart(),
            'order' => $order,
            'address' => $order->getAddress()
        ]);
    }
}
