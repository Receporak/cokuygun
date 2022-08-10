<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Service\DataTypeConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        // Kategorileri ana kategorilerine bağlı olacak şekilde listleme fonksiyonu
        $categoryList = $categoryRepository->getAllCategoryForSelect([])["formTypeData"];

        return $this->render('category/index.html.twig', [
            'categories' => $categoryList,
        ]);
    }

    #[Route('/new', name: 'category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CategoryRepository $categoryRepository): Response
    {
        // Kategorileri ana kategorilerine bağlı olacak şekilde listleme fonksiyonu
        $categoryList = $categoryRepository->getAllCategoryForSelect([]);
        $category = new Category();
        // Listelenen kategorileri forma gönderme
        $category->parentCategoryList = $categoryList["formTypeData"];
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categoryRes = $categoryRepository->newCategory($request->request->all()["category"], $category);
            if (!$categoryRes["isSuccess"]) {
                $this->addFlash("error", "Kategori oluşturma işlemi başarısız oldu. Hata: " . $categoryRes["message"]);
            }
            return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, CategoryRepository $categoryRepository): Response
    {
        // Kategorileri ana kategorilerine bağlı olacak şekilde listeleme fonksiyonu ve istenmeyen kategoriyi listeden kaldırmak için deniedCategoryId değişkenini kullanıyoruz
        $categoryList = $categoryRepository->getAllCategoryForSelect(["deniedCategoryId" => $category->getId()]);

        $category->parentCategoryList = $categoryList["formTypeData"];
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $categoryRepository->add($category, true);
            } catch (\Exception $e) {
                $this->addFlash("error", "Kategori güncelleme işlemi başarısız oldu. Hata: " . $e->getMessage());
            }

            return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, CategoryRepository $categoryRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            try {
                if (count($category->getProducts()->toArray()) > 0) {
                    $this->addFlash("danger", "Bu kategoriye ait ürünler var. Lütfen ürünleri silip tekrar deneyiniz.");
                    return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
                } else {
                    $categoryRepository->remove($category, true);
                }
            } catch (\Exception $e) {
                $this->addFlash('danger', "Kategori silinemedi. Bu kategoriye bağlı kategoriler bulunmaktadır.");
            }
        }

        return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
    }
}
