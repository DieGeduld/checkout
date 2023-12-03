<?php

namespace App\Entity;

use App\Repository\ShoppingCartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShoppingCartRepository::class)]
class ShoppingCart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'shoppingCart', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'shoppingcart', targetEntity: ShoppingCartProduct::class)]
    private Collection $shoppingCartProducts;

    #[ORM\Column(length: 255)]
    private ?string $state = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $sessionId;

    public function __construct()
    {
        $this->shoppingCartProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getShoppingCartProducts(): Collection
    {
        return $this->shoppingCartProducts;
    } 

    public function addShoppingCartProduct(ShoppingCartProduct $shoppingCartProduct): static
    {
        if (!$this->shoppingCartProducts->contains($shoppingCartProduct)) {
            $this->shoppingCartProducts[] = $shoppingCartProduct;
            $shoppingCartProduct->setShoppingcart($this);
        }

        return $this;
    }

}
