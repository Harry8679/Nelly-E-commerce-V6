<?php

namespace App\Classe;

use Symfony\Component\HttpFoundation\RequestStack;

class Cart
{
    public function __construct(private RequestStack $requestStack)
    {
        
    }
    
    public function add($product)
    {
        // Appeler la session de Symfony
        $cart = $this->requestStack->getSession()->get('cart', []); // Initialiser avec un tableau vide si la session est vide

        // Vérifier si l'entrée est valide
        if (isset($cart[$product->getId()]) && is_array($cart[$product->getId()])) {
            // Ajouter une quantité +1 au produit existant
            $cart[$product->getId()]['quantity'] += 1;
        } else {
            // Initialiser le produit avec une quantité de 1
            $cart[$product->getId()] = [
                'object' => $product,
                'quantity' => 1,
            ];
        }

        // Créer ma session Cart
        $this->requestStack->getSession()->set('cart', $cart);
    }

    public function decrease($id)
    {
        $cart = $this->requestStack->getSession()->get('cart');

        if (isset($cart[$id]) && $cart[$id]['quantity'] > 1) {
            $cart[$id]['quantity']--; // Décrémente uniquement la quantité
        } else {
            unset($cart[$id]); // Supprime l'entrée si la quantité tombe à 0
        }

        $this->requestStack->getSession()->set('cart', $cart);
    }

    public function fullQuantity()
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        $quantity = 0;

        if (!isset($cart)) {
            return $quantity;
        }

        foreach ($cart as $product) {
            $quantity = $quantity + $product['quantity'];
        }

        return $quantity;
    }

    public function getTotalWithTaxes()
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        $price = 0;

        if (!isset($cart)) {
            return $price;
        }

        foreach ($cart as $product) {
            // Calcul avec le prix TTC (getPriceWithTaxes)
            $price += $product['object']->getPriceWithTaxes() * $product['quantity'];
        }

        // Arrondir le total à deux décimales
        return round($price, 2);
    }

    public function remove()
    {
        return $this->requestStack->getSession()->remove('cart');
    }

    public function getCart()
    {
        $cart = $this->requestStack->getSession()->get('cart', []);

        // Filtrer les entrées invalides
        foreach ($cart as $id => $item) {
            if (!is_array($item) || !isset($item['object'], $item['quantity'])) {
                unset($cart[$id]); // Supprimer les données incorrectes
            }
        }

        return $cart;
    }
}