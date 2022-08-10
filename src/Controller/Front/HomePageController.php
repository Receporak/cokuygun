<?php

namespace App\Controller\Front;

use App\Form\AddressType;
use App\Repository\CategoryRepository;
use App\Repository\OrderProductRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\ShoppingCartRepository;
use App\Service\DataTypeConverter;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class HomePageController extends AbstractController
{
    /**
     * @Route("/", name="home_page")
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     * @param DataTypeConverter $dataTypeConverter
     * @param PaginatorInterface $paginator
     * @param ShoppingCartRepository $shoppingCartRepository
     * @return Response
     */
    public function index(CategoryRepository $categoryRepository,ProductRepository $productRepository,DataTypeConverter $dataTypeConverter,PaginatorInterface $paginator,ShoppingCartRepository $shoppingCartRepository)
    {
        if ($this->getUser()) {
            $shoppingCart = $shoppingCartRepository->getCartItems([
                'user' => $this->getUser()->getId()
            ])['data'];
        } else {
            $shoppingCart = [];
        }
        return $this->render('front_pages/home_page.html.twig',[
            'categoryList' => $dataTypeConverter->toJsonString($categoryRepository->getCategories([])['data']) ,
            'productList' => $dataTypeConverter->toJsonString($productRepository->getProducts([],$paginator)['data']),
            'shoppingCart' => $dataTypeConverter->toJsonString($shoppingCart),
            'uploadDirectory' => $this->getParameter('uploadDirectory')
        ]);
    }

    /**
     * @Route("/product-list", name="product_list")
     * @param CategoryRepository $categoryRepository
     * @param ShoppingCartRepository $shoppingCartRepository
     * @param ProductRepository $productRepository
     * @param DataTypeConverter $dataTypeConverter
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function productList(CategoryRepository $categoryRepository,ShoppingCartRepository $shoppingCartRepository,ProductRepository $productRepository,DataTypeConverter $dataTypeConverter,PaginatorInterface $paginator)
    {
        if ($this->getUser()) {
            $shoppingCart = $shoppingCartRepository->getCartItems([
                'user' => $this->getUser()->getId()
            ])['data'];
        } else {
            $shoppingCart = [];
        }
        return $this->render('front_pages/product_list.html.twig',[
            'categoryList' => $dataTypeConverter->toJsonString($categoryRepository->getCategories([])['data']) ,
            'productList' => $dataTypeConverter->toJsonString($productRepository->getProducts([],$paginator)['data']) ,
            'shoppingCart' => $dataTypeConverter->toJsonString($shoppingCart)

        ]);
    }

    /**
     * @Route("/shopping-cart", name="shopping_cart")
     * @param ShoppingCartRepository $shoppingCartRepository
     * @param DataTypeConverter $dataTypeConverter
     * @return Response
     */
    public function shoppingCart(ShoppingCartRepository $shoppingCartRepository,DataTypeConverter $dataTypeConverter): Response
    {
        return $this->render('front_pages/shopping_cart.html.twig',[
            'shoppingCart' => $dataTypeConverter->toJsonString($shoppingCartRepository->getCartItems(["user"=>$this->getUser()->getId()])['data']) ,
            'amount' => $dataTypeConverter->toJsonString($shoppingCartRepository->cartTotalAmount(["user"=>$this->getUser()->getId()])['data']) ,
        ]);
    }

    /**
     * @Route("/payment-done", name="payment_done")
     * @return Response
     */
    public function paymentDone(): Response
    {
        return $this->render('front_pages/payment_done.html.twig');
    }
    /**
     * @Route("/payment-error", name="payment_error")
     * @return Response
     */
    public function paymentError(): Response
    {
        return $this->render('front_pages/payment_error.html.twig');
    }

    /**
     * @Route("/cart-confirm", name="cart_confirm", methods={"GET","POST"})
     * @param Request $request
     * @param ShoppingCartRepository $shoppingCartRepository
     * @param DataTypeConverter $dataTypeConverter
     * @param OrderRepository $orderRepository
     * @param OrderProductRepository $orderProductRepository
     * @return Response
     */
    public function cartConfirm(Request $request,ShoppingCartRepository $shoppingCartRepository,DataTypeConverter $dataTypeConverter,OrderRepository $orderRepository,OrderProductRepository $orderProductRepository): Response
    {
        $user=$this->getUser();
        $amount=$shoppingCartRepository->cartTotalAmount(["user"=>$user->getId()])['data'];
        $form = $this->createForm(AddressType::class);
        $form->handleRequest($request);
        if ($request->getMethod()=="GET"){
            if ($amount['subTotalAmount']==0){
                return $this->redirectToRoute('home_page');
            }
        }
        if($form->isSubmitted() && $form->isValid()) {
            $address =$request->request->all()['address'];
            $addressString = $address['name'].' '.$address['surname'].' '.$address['fullAddress'].' '.$address['district'].'/'.$address['city'].' tel: '.$address['phone'].' email: '.$address['email'];
            $postData['user'] = $user->getId();
            $postData['address'] = $addressString;
            $postData['totalAmount'] = $amount['totalAmount'];
            $postData['discountAmount'] = $amount['discountAmount'];

            $order= $orderRepository->newOrder($postData, $shoppingCartRepository,  $orderProductRepository);
            if ($order['isSuccess']) {
                $shoppingCartRepository->deleteCartItems(["user"=>$user->getId()]);
//                $this->addFlash('success', 'Siparişiniz başarıyla alındı. Teşekkür ederiz.');
                return $this->redirectToRoute('payment_done');
            } else {
//                $this->addFlash('error', 'Siparişiniz alınamadı. Lütfen tekrar deneyiniz.');
                return $this->redirectToRoute('payment_error');
            }
        }
        return $this->render('front_pages/cart_confirm.html.twig',[
            'form' => $form->createView(),
            'shoppingCart' => $dataTypeConverter->toJsonString($shoppingCartRepository->getCartItems(["user"=>$user->getId()])['data']) ,
            'amount' => $dataTypeConverter->toJsonString($amount) ,
        ]);
    }
}