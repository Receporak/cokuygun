<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\OrderProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: OrderProductRepository::class)]
#[ApiResource]
class OrderProduct
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?float $unitPrice = null;

    #[ORM\ManyToOne(inversedBy: 'orderProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $orderr = null;

    #[ORM\ManyToOne(inversedBy: 'orderProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column]
    private ?bool $hasCampaignDiscount = false;

    #[ORM\Column]
    private ?bool $hasDiscount = false;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getUnitPrice(): ?float
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(float $unitPrice): self
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function getOrderr(): ?Order
    {
        return $this->orderr;
    }

    public function setOrderr(?Order $orderr): self
    {
        $this->orderr = $orderr;

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

    public function isHasCampaignDiscount(): ?bool
    {
        return $this->hasCampaignDiscount;
    }

    public function setHasCampaignDiscount(bool $hasCampaignDiscount): self
    {
        $this->hasCampaignDiscount = $hasCampaignDiscount;

        return $this;
    }

    public function isHasDiscount(): ?bool
    {
        return $this->hasDiscount;
    }

    public function setHasDiscount(bool $hasDiscount): self
    {
        $this->hasDiscount = $hasDiscount;

        return $this;
    }

}
