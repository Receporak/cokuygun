<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ShoppingCart;
use App\Entity\User;
use App\Service\DataTypeConverter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function PHPUnit\Framework\throwException;

/**
 * @extends ServiceEntityRepository<ShoppingCart>
 *
 * @method ShoppingCart|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShoppingCart|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShoppingCart[]    findAll()
 * @method ShoppingCart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShoppingCartRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShoppingCart::class);
    }

    public function add(ShoppingCart $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ShoppingCart $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    /**
     * @param array $postData
     * @param ProductRepository $productRepository
     * @return array
     */
    public function addToCart(array $postData, ProductRepository $productRepository): array
    {
        $result = ["isSuccess" => false, "message" => "No action taken", "data" => []];
        $em = $this->getEntityManager();
        $shoppingCartItems = [];
        try {
            $owner = $em->find(User::class, (int)$postData["user"]);
            $product = $em->find(Product::class, (int)$postData["product"]);
            $cartItem = $em->getRepository(ShoppingCart::class)->findOneBy(["product" => $product, "user" => $owner]);
            if ($owner) {
                if ($product) {
                    if (!$cartItem) {
                        $cartItem = new ShoppingCart();
                        $cartItem
                            ->setProduct($product)
                            ->setUser($owner)
                            ->setQuantity(1);

                    } else {
                        $cartItem->setQuantity($cartItem->getQuantity() + 1);
                    }
                    $this->add($cartItem, true);

                    // Ürün Kampanyası var mı?
                    $campaignCheck = $productRepository->discountCheck($product->getId());

                    // Ürün Kampanyası varsa bedava ürün ekle
                    if ($campaignCheck["isSuccess"]) {
                        if ($cartItem->getQuantity() == 3) {
                            $cartItem
                                ->setQuantity($cartItem->getQuantity() + 1)
                                ->setHasCampaignDiscount(1);
                            $this->add($cartItem, true);
                        }
                    }

                    // Minimum fiyatlı 2.ürüne indirim uygulama
                    $this->setDiscountToCartItem($owner->getId());
                }
            }

            $result["isSuccess"] = true;
            $result["message"] = "Action taken";
            $result["data"] = isset($owner) ? $this->getCartItems(["user" => $owner->getId()])["data"] : [];
        } catch (\Exception $e) {
            $result["message"] = $e->getMessage();
        }
        return $result;
    }

    /**
     * @param array $postData
     * @return array
     */
    public function getCartItems(array $postData): array
    {
        $result = ["isSuccess" => false, "message" => "No action taken", "data" => []];
        try {
            $cartItem = $this->createQueryBuilder("sc");
            $cartItem
                ->select("sc.id,sc.quantity", "sc.isDiscounted", "sc.hasCampaignDiscount")
                ->addSelect("p.id as productId,p.name as productName,p.price as productPrice")
                ->addSelect("ifelse(sc.hasCampaignDiscount=true,
                                            ifelse(sc.isDiscounted=true,
                                                ROUND(((sc.quantity-1)*p.price)-(p.price/2),2),
                                                ROUND((sc.quantity-1)*p.price,2)
                                                ),
                                            ifelse(sc.isDiscounted=true,
                                                ROUND((sc.quantity*p.price)-(p.price/2),2),
                                                ROUND(sc.quantity*p.price,2)
                                                )
                                        ) as productTotalPrice")
                ->addSelect("ROUND(sc.quantity*p.price,2) as withoutDiscountTotalPrice")
                ->addSelect("(SELECT SUM(sc1.quantity) FROM " . ShoppingCart::class . " sc1 WHERE sc1.user = " . $postData['user'] . ") as totalQuantity");
            $cartItem->leftJoin("sc.product", "p");
            $cartItem
                ->where("sc.user = :user")
                ->setParameter("user", $postData["user"]);
            $cartItem = $cartItem
                ->getQuery()
                ->getArrayResult();
            $cartItem = array_column($cartItem, null, 'id');
            $result["isSuccess"] = true;
            $result["message"] = "Action taken";
            $result["data"] = $cartItem;
        } catch (\Exception $e) {
            $result["message"] = $e->getMessage();
        }
        return $result;
    }

    /**
     * @param array $postData
     * @return array
     */
    public function deleteCartItems(array $postData): array
    {
        $result = ["isSuccess" => false, "message" => "No action taken", "data" => []];
        try {
            $cartItem = $this->createQueryBuilder("sc");
            $cartItem
                ->delete()
                ->where("sc.user = :user")
                ->setParameter("user", $postData["user"]);
            $cartItem = $cartItem
                ->getQuery()
                ->getArrayResult();

            $result["isSuccess"] = true;
            $result["message"] = "Action taken";
            $result["data"] = $cartItem;
        } catch (\Exception $e) {
            $result["message"] = $e->getMessage();
        }
        return $result;
    }
    /**
     * @param array $data
     * @return array
     */
    public function findMinPriceItemInArray(array $data): array
    {
        $minPriceItem = array_values($data)[0];
        foreach ($data as $item) {
            if ($minPriceItem["productPrice"] > $item["productPrice"]) {
                $minPriceItem = $item;
            }
        }
        return $minPriceItem;
    }

    /**
     * @param array $postData
     * @param ProductRepository $productRepository
     * @return array
     */
    public function cartItemProcessor(array $postData, ProductRepository $productRepository): array
    {
        $result = ["isSuccess" => false, "message" => "No action taken", "data" => []];
        $em = $this->getEntityManager();
        try {
            $owner = $em->find(User::class, (int)$postData["user"]);
            $cartItem = $em->getRepository(ShoppingCart::class)->findOneBy(["product" => $postData["productId"], "user" => $owner]);
            if ($owner) {
                if (!$cartItem) {
                    $result["message"] = "No cart found";
                    return $result;
                } else {
                    if (isset($postData["action"])) {
                        switch ($postData["action"]) {
                            // ürün adet arttırma işlemi
                            case "increase":
                                if ($cartItem->getQuantity() == $cartItem->getProduct()->getStock()) {
                                    $result["message"] = "Ürün stok yetersiz";
                                    return $result;
                                } else {
                                    if ($cartItem->getQuantity() == 2) {
                                        $campaignCheck = $productRepository->discountCheck($cartItem->getProduct()->getId());
                                        if ($campaignCheck["isSuccess"]) {
                                            $cartItem->setQuantity($cartItem->getQuantity() + 2)
                                                ->setHasCampaignDiscount(true);
                                        } else {
                                            $cartItem->setQuantity($cartItem->getQuantity() + 1);
                                        }
                                    } else {
                                        $cartItem->setQuantity($cartItem->getQuantity() + 1);
                                    }
                                }
                                break;
                            // ürün adet azaltma işlemi
                            case "decrease":
                                if ($cartItem->getQuantity() > 1) {
                                    if ($cartItem->isHasCampaignDiscount() && $cartItem->getQuantity() == 4) {
                                        $cartItem->setQuantity($cartItem->getQuantity() - 2)
                                            ->setHasCampaignDiscount(false);
                                    } else {
                                        $cartItem->setQuantity($cartItem->getQuantity() - 1);
                                    }
                                } elseif ($cartItem->getQuantity() == 1) {
                                    $this->remove($cartItem, true);
                                    $this->setDiscountToCartItem($owner->getId());
                                    $result["amount"] = $this->cartTotalAmount(["user" => $owner->getId()])["data"];
                                    $result["isSuccess"] = true;
                                    $result["message"] = "Action taken";
                                    return $result;
                                }
                                break;
                            // ürün silme işlemi
                            case "remove":
                                $this->remove($cartItem, true);
                                $this->setDiscountToCartItem($owner->getId());
                                $result["amount"] = $this->cartTotalAmount(["user" => $owner->getId()])["data"];

                                $result["isSuccess"] = true;
                                $result["message"] = "Action taken";
                                return $result;
                            default:
                                $result["message"] = "No action taken";
                                break;
                        }
                        $this->add($cartItem, true);
                        $this->setDiscountToCartItem($owner->getId());
                    }
                }
            }

            $result["isSuccess"] = true;
            $result["message"] = "Action taken";
            $result["data"] = $this->getCartItems(["user" => $owner->getId()])["data"];
            $result["amount"] = $this->cartTotalAmount(["user" => $owner->getId()])["data"];
        } catch (\Exception $e) {
            $result["message"] = $e->getMessage();
        }
        return $result;
    }

    /**
     * @param int $userId
     * @return void
     */
    public function setDiscountToCartItem(int $userId): void
    {
        $shoppingCartItems = $this->getCartItems(["user" => $userId])["data"];
        if (count($shoppingCartItems) > 0 && array_values($shoppingCartItems)[0]["totalQuantity"] > 1) {
            //TODO: 2.ürüne indirim uygulama kontrolü
            $minPriceItem = $this->findMinPriceItemInArray($shoppingCartItems);
            try {
                if (!$minPriceItem["isDiscounted"]) {
                    $discountedShoppingCartItem = $this->find($minPriceItem["id"]);
                    if ($discountedShoppingCartItem) {
                        $queryRes = $this->createQueryBuilder("sc")
                            ->update()
                            ->set("sc.isDiscounted", ":isDiscounted")
                            ->where("sc.user = :userId")
                            ->setParameter("userId", $userId)
                            ->setParameter("isDiscounted", false)
                            ->getQuery()
                            ->execute();
                        $discountedShoppingCartItem->setIsDiscounted(true);
                        $this->add($discountedShoppingCartItem, true);
                    }
                }

            } catch (\Exception $e) {
            }
        }


    }

    /**
     * @param array $postData
     * @return array
     */
    public function cartTotalAmount(array $postData): array
    {
        $result = ["isSuccess" => false, "message" => "No action taken", "data" => []];
        $em = $this->getEntityManager();
        try {
            $shoppingCartItems = $this->getCartItems(["user" => $postData["user"]])["data"];
            $subTotalAmount = 0;
            $totalAmount = 0;
            $discountAmount = 0;
            if (count($shoppingCartItems) > 0) {
                foreach ($shoppingCartItems as $shoppingCartItem) {
                    $subTotalAmount += $shoppingCartItem["withoutDiscountTotalPrice"];
                    $totalAmount += $shoppingCartItem["productTotalPrice"];

                }
                $discountAmount = $subTotalAmount - $totalAmount;
            }

            $result["isSuccess"] = true;
            $result["message"] = "Action taken";
            $result["data"] = [
                "subTotalAmount" => number_format($subTotalAmount,2),
                "discountAmount" => number_format($discountAmount,2),
                "totalAmount" => number_format($totalAmount,2),
            ];
        } catch (\Exception $e) {
            $result["message"] = $e->getMessage();
        }
        return $result;
    }
}
