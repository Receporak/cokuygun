<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\DataTypeConverter;
use App\Service\FileUploaderRemover;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository, FileUploaderRemover $fileUploader): Response
    {
        $categoryList= $categoryRepository->getAllCategoryForSelect([]);
        $product = new Product();
        $product->categoryList = $categoryList["formTypeData"];
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $postData=[];
            $postData = array_merge($postData, $request->request->all()["product"]);
            $postData = array_merge($postData, $request->files->all()["product"]);
            $productRepository->newProduct($postData,$product,$categoryRepository,$fileUploader);
            return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, ProductRepository $productRepository, CategoryRepository $categoryRepository, DataTypeConverter $dataTypeConverter, FileUploaderRemover $fileUploader): Response
    {
        $categoryList= $categoryRepository->getAllCategoryForSelect([]);
        $product->categoryList = $categoryList["formTypeData"];
        $product->isUpdate = true;
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($request->isMethod("GET")){
            $form->get("oldImage")->setData($product->getImage());
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $postData=[];
            $postData = array_merge($postData, $request->request->all()["product"]);
            $postData = array_merge($postData, $request->files->all()["product"]);
            $productRepository->updateProduct($postData,$product,$categoryRepository,$fileUploader);
            return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
            'selectedCategory'=>$dataTypeConverter->toJsonString($product->getCategory()->toArray())
        ]);
    }

    #[Route('/{id}', name: 'product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            try {
                $productRepository->remove($product, true);
            }catch (\Exception $e){
                $this->addFlash('danger', 'Bu ürünü silemezsiniz. Ürünün bağlı olduğu tablolar var. Eğer tablolara bağlı olmadığınızdan eminseniz sepete eklenmiş ürün olabilir.');
                return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);

            }
        }

        return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
    }
}
