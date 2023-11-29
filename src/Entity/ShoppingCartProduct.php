<?php

namespace App\Entity;

use App\Repository\ShoppingCartProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShoppingCartProductRepository::class)]
class ShoppingCartProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'shoppingCartProducts')]
    private ?ShoppingCart $shoppingcart = null;

    #[ORM\ManyToOne(inversedBy: 'shoppingCartProducts')]
    private ?Product $product = null;

    #[ORM\Column]
    private ?int $quantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShoppingcart(): ?ShoppingCart
    {
        return $this->shoppingcart;
    }

    public function setShoppingcart(?ShoppingCart $shoppingcart): static
    {
        $this->shoppingcart = $shoppingcart;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }
}
