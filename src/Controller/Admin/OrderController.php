<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\OrderProductRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/order')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'order_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
        ]);
    }



    #[Route('/{id}', name: 'order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order, OrderRepository $orderRepository,OrderProductRepository $orderProductRepository): Response
    {
        // Siparişler listelenir.
        $orderProducts=$orderProductRepository->getOrderProducts([
            'order' => $order->getId()
        ]);
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $orderRepository->add($order, true);

            return $this->redirectToRoute('order_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
            'orderProducts' => $orderProducts["data"]
        ]);
    }

    #[Route('/{id}', name: 'order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, OrderRepository $orderRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $orderRepository->remove($order, true);
        }

        return $this->redirectToRoute('order_index', [], Response::HTTP_SEE_OTHER);
    }
}
