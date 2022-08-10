<?php

namespace App\Repository;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderState;
use App\Entity\Product;
use App\Entity\ShoppingCart;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function add(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function newOrder(array $postData, ShoppingCartRepository $shoppingCartRepository, OrderProductRepository $orderProductRepository)
    {
        $result = ["isSuccess" => false, "message" => "No action taken", "data" => []];
        $em = $this->getEntityManager();
        try {
            $owner = $em->find(User::class, (int)$postData["user"]);
            $shoppingCartList = $shoppingCartRepository->getCartItems(["user" => $postData["user"]]);
            if ($owner) {
                if ($shoppingCartList["isSuccess"] && count($shoppingCartList["data"]) > 0) {
                    $order = new Order();
                    $order
                        ->setUser($owner)
                        ->setOrderState($em->find(OrderState::class, 1))
                        ->setAddress($postData["address"])
                        ->setCode($this->recursiveOrder())
                        ->setPaidPrice((float)$postData["totalAmount"])
                        ->setDiscountAmount(isset($postData["discountAmount"]) ? (float)$postData["discountAmount"] : 0)
                        ->setCreatedAt(new \DateTime())
                        ->setUpdatedAt(new \DateTime());
                    $this->add($order);
                    $orderProduct = $orderProductRepository->newOrderProducts([
                        "order" => $order,
                        "user" => $owner
                    ], $shoppingCartRepository);
                    if ($orderProduct["isSuccess"]) {
                        $em->flush();
                    } else {
                        $result["message"] = $orderProduct["message"];
                        return $result;
                    }
                } else {
                    $result["message"] = "Product not found in cart";
                    return $result;
                }
            } else {
                $result["message"] = "User not found";
                return $result;
            }
            $result["isSuccess"] = true;
            $result["message"] = "Action taken";
            $result["data"] = [];
        } catch (\Exception $e) {
            $result["message"] = $e->getMessage();
        }
        return $result;
    }



    /**
     * @return string
     */
    public function recursiveOrder()
    {
        $em = $this->getEntityManager();
        $seed = str_split('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        shuffle($seed);
        $hrand = '';
        foreach (array_rand($seed, 6) as $k) $hrand .= $seed[$k];
        $randOrderId = $em->getRepository(Order::class)->findBy(['code' => $hrand]);
        if (count($randOrderId) > 1) {
            return $this->recursiveOrder();
        } else {
            return $hrand;
        }
    }
}
