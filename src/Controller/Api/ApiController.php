<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\OrderProductRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\ShoppingCartRepository;
use Knp\Component\Pager\PaginatorInterface;
use Prophecy\Argument\Token\TokenInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

// Sayfa yenilenmeden dataları getirmek için api ucu yazıldı.
#[Route("/api", name: "front_api_")]
class ApiController extends AbstractController
{

    // Sepete ekleme işlemi yapıldı.
    #[Route("/add-to-cart", name: "add_to_cart", methods: ['POST'])]
    public function addToCart(Request $request, ShoppingCartRepository $shoppingCartRepository, ProductRepository $productRepository,UserInterface $user): JsonResponse
    {
        $postData = [];
        $jsonData = json_decode($request->getContent(), true);
        if (!is_null($jsonData)) $postData = $jsonData;
        $postData = array_merge($postData, $request->query->all());
        $postData["user"] = $user->getId();
        if (!isset($postData["product"])) {
            return $this->json(["isSuccess" => false, "message" => "missing field: product", "data" => []]);
        }
        return $this->json($shoppingCartRepository->addToCart($postData,$productRepository));
    }

    // Sepeti getirme işlemi yapıldı.
    #[Route("/get-cart-items", name: 'get_cart_items', methods: ['POST'])]
    public function getCartItems(Request $request, ShoppingCartRepository $shoppingCartRepository, UserInterface $user): JsonResponse
    {
        $postData = [];
        $jsonData = json_decode($request->getContent(), true);
        if (!is_null($jsonData)) $postData = $jsonData;
        $postData = array_merge($postData, $request->query->all());
        /** @var User $user */
        $postData['user'] = $user->getId();
        return $this->json($shoppingCartRepository->getCartItems($postData));
    }

    // Sepette arttırma, azaltma ve silme işlemi yapıldı.
    #[Route("/cart-item-processor" , name: 'cart_item_processor', methods: ['POST'])]
    public function cartItemProcessor(Request $request, ShoppingCartRepository $shoppingCartRepository, UserInterface $user,ProductRepository $productRepository): JsonResponse
    {
        $postData = [];
        $jsonData = json_decode($request->getContent(), true);
        if (!is_null($jsonData)) $postData = $jsonData;
        $postData = array_merge($postData, $request->query->all());
        /** @var User $user */
        $postData['user'] = $user->getId();
        return $this->json($shoppingCartRepository->cartItemProcessor($postData,$productRepository));
    }

    // Kategorileri getirme işlemi yapıldı.
    #[Route("/get-categories",name: 'get_categories', methods: ['POST'])]
    public function getCategories(Request $request,CategoryRepository $categoryRepository): JsonResponse
    {
        $postData = [];
        $jsonData = json_decode($request->getContent(), true);
        if (!is_null($jsonData)) $postData = $jsonData;
        $postData = array_merge($postData, $request->query->all());
        return $this->json($categoryRepository->getCategories($postData));
    }

    // Ürünleri getirme işlemi yapıldı.
    #[Route("/get-products",name: 'get_products', methods: ['POST'])]
    public function getProducts(Request $request,ProductRepository $productRepository,PaginatorInterface $paginator): JsonResponse
    {

        $postData = [];
        $jsonData = json_decode($request->getContent(), true);
        if (!is_null($jsonData)) $postData = $jsonData;
        $postData = array_merge($postData, $request->query->all());
        return $this->json($productRepository->getProducts($postData,$paginator));
    }


}