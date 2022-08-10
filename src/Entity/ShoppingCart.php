<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ShoppingCartRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: ShoppingCartRepository::class)]
#[ApiResource]
class ShoppingCart
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'shoppingCarts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'shoppingCarts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?bool $isDiscounted = false;

    #[ORM\Column]
    private ?bool $hasCampaignDiscount = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function isIsDiscounted(): ?bool
    {
        return $this->isDiscounted;
    }

    public function setIsDiscounted(bool $isDiscounted): self
    {
        $this->isDiscounted = $isDiscounted;

        return $this;
    }

    public function isHasCampaignDiscount(): ?bool
    {
        return $this->hasCampaignDiscount;
    }

    public function setHasCampaignDiscount(bool $hasCampaignDiscount): self
    {
        $this->hasCampaignDiscount = $hasCampaignDiscount;

        return $this;
    }
}
