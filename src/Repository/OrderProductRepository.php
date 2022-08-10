<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderProduct>
 *
 * @method OrderProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderProduct[]    findAll()
 * @method OrderProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderProduct::class);
    }

    public function add(OrderProduct $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OrderProduct $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function newOrderProducts(array $postData, ShoppingCartRepository $shoppingCartRepository): array
    {
        $result = ["isSuccess" => false, "message" => "No action taken", "data" => []];
        $em = $this->getEntityManager();
        try {
            /** @var User $owner */
            $owner = $postData["user"];
            if ($owner) {
                // Sepettteki ürünler getirilerek orderProduct tablosuna eklenir.
            $shoppingCartList = $shoppingCartRepository->getCartItems(["user" => $owner->getId()]);
                if ($shoppingCartList["isSuccess"] && count($shoppingCartList["data"]) > 0) {
                    foreach ($shoppingCartList["data"] as $shoppingCart) {
                        $orderProduct = new OrderProduct();
                        $orderProduct
                            ->setOrderr($postData["order"])
                            ->setProduct($em->find(Product::class, (int)$shoppingCart["productId"]))
                            ->setUnitPrice((floatval($shoppingCart["productPrice"])))
                            ->setHasCampaignDiscount((bool)$shoppingCart["hasCampaignDiscount"])
                            ->setHasDiscount((bool)$shoppingCart["isDiscounted"])
                            ->setQuantity((int)$shoppingCart["quantity"]);
                        $this->add($orderProduct);
                    }
                } else {
                    $result["message"] = "No products found in shopping cart";
                    return $result;
                }
            } else {
                $result["message"] = "User not found";
                return $result;
            }
            $result["isSuccess"] = true;
            $result["message"] = "Order products added successfully";
            $result["data"] = [];
        } catch (\Exception $e) {
            $result["message"] = $e->getMessage();
        }
        return $result;
    }

    public function getOrderProducts(array $postData): array
    {
        $result = ["isSuccess" => false, "message" => "No action taken", "data" => []];
        try {
            $orderProducts = $this->createQueryBuilder("op")
                ->select("op.id", "op.quantity", "op.unitPrice", "op.hasCampaignDiscount", "op.hasDiscount")
                ->addSelect("p.name")
                // Sipariş edilen ürünlerde kampanya kullanılmışsa, kampanya indirimini hesapla
                ->addSelect("ifelse(op.hasCampaignDiscount=true,
                                            ifelse(op.hasDiscount=true,
                                                ROUND(((op.quantity-1)*op.unitPrice)-(op.unitPrice/2),2),
                                                ROUND((op.quantity-1)*op.unitPrice,2)
                                                ),
                                            ifelse(op.hasDiscount=true,
                                                ROUND((op.quantity*op.unitPrice)-(op.unitPrice/2),2),
                                                ROUND(op.quantity*op.unitPrice,2)
                                                )
                                        ) as productTotalPrice")
                ->addSelect("ROUND(op.quantity*op.unitPrice,2) as withoutDiscountTotalPrice")
                ->leftJoin("op.orderr", "o")
                ->leftJoin("op.product", "p")
                ->where("o.id = :order")
                ->setParameter("order", $postData["order"])
                ->getQuery()
                ->getResult();
            if ($orderProducts) {
                $result["isSuccess"] = true;
                $result["message"] = "Action taken";
                $result["data"] = $orderProducts;
            } else {
                $result["message"] = "Order products not found";
            }
        } catch (\Exception $e) {
            $result["message"] = $e->getMessage();
        }
        return $result;
    }
}
