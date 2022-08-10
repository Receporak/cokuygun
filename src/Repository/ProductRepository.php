<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use App\Service\FileUploaderRemover;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function add(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getProducts(array $postData, PaginatorInterface $paginator): array
    {
        $result = ["isSuccess" => false, "message" => "No action", "data" => null];
        try {
            $products = $this->createQueryBuilder('p');
            $products->select('p.id', 'p.name', 'p.description', ' p.image', 'p.price');
            $products->leftJoin('p.category', 'c');
            if (isset($postData["category"]) && $postData["category"] != "") {
                $products->where('c.id = :category')
                    ->setParameter('category', $postData["category"]);
            }
            $products = $products
                ->groupBy('p.id');
            $pagination = $paginator->paginate(
                $products->getQuery(),
                $postData['page'] ?? 1,
                $postData['limit'] ?? 999
            );

            $result = ["isSuccess" => true, "message" => "Action taken", "data" => $pagination->getItems()];
        } catch (\Exception $e) {
            $result["message"] = $e->getMessage();
        }
        return $result;

    }

    /**
     * @param array $postData
     * @param Product $product
     * @param CategoryRepository $categoryRepository
     * @param FileUploaderRemover $fileUploader
     * @return array
     */
    public function newProduct(array $postData, Product $product, CategoryRepository $categoryRepository, FileUploaderRemover $fileUploader): array
    {
        $result = ["isSuccess" => false, "message" => "No action", "data" => null];
        try {

            if (!isset($postData["category"]) || !is_array($postData["category"])) {
                $result["message"] = "Category is required";
                return $result;
            }
            foreach ($postData["category"] as $category) {
                // Seçili olan kategorileri tüm üst kategorileri ürüne eklenir.
                $allParentCategory = $categoryRepository->getMultiMainCategory($category);
                foreach ($allParentCategory as $parentCategory) {
                    $product->addCategory($categoryRepository->find($parentCategory["id"]));
                }
            }
            // Ürüne ait görsel sisteme yüklenir.
            if ($postData["image"] != "") {
                $imageFile = $fileUploader->upload($postData["image"]);
                if ($imageFile["isSuccess"]) {
                    $product->setImage($imageFile["data"]);
                } else {
                    $result["message"] = $imageFile["message"];
                    return $result;
                }
            }
            try {
                $this->add($product, true);
            } catch (\Exception $e) {
                $result["message"] = $e->getMessage();
                return $result;
            }
            $result["isSuccess"] = true;
            $result["message"] = "Product created";
        } catch (\Exception $e) {
            $result["message"] = $e->getMessage();
        }
        return $result;
    }

    /**
     * @param array $postData
     * @param Product $product
     * @param CategoryRepository $categoryRepository
     * @param FileUploaderRemover $fileUploader
     * @return array
     */
    public function updateProduct(array $postData, Product $product, CategoryRepository $categoryRepository, FileUploaderRemover $fileUploader): array
    {
        $result = ["isSuccess" => false, "message" => "No action", "data" => null];
        try {

            if (!isset($postData["category"]) || !is_array($postData["category"])) {
                $result["message"] = "Category is required";
                return $result;
            }
            // Eğer ürünün kategorileri değiştirilmişse, ürünün kategorileri silinir.
            foreach ($product->getCategory()->toArray() as $category) {
                $product->removeCategory($category);
            }

            // Seçili olan kategorileri tüm üst kategorileri ürüne eklenir.
            foreach ($postData["category"] as $category) {
                $allParentCategory = $categoryRepository->getMultiMainCategory($category);
                foreach ($allParentCategory as $parentCategory) {
                    $product->addCategory($categoryRepository->find($parentCategory['id']));
                }
            }
            if ($postData["image"] != "") {
                $imageFile = $fileUploader->upload($postData["image"]);
                if ($imageFile["isSuccess"]) {
                    $product->setImage($imageFile["data"]);
                    if ($postData["oldImage"] != "") {
                        try {
                            $fileUploader->deleteImage($postData["oldImage"]);
                        } catch (\Exception $e) {
                        }
                    }
                } else {
                    $result["message"] = $imageFile["message"];
                    return $result;
                }
            }
            $this->add($product, true);

            $result["isSuccess"] = true;
            $result["message"] = "Product created";
        } catch (\Exception $e) {
            $result["message"] = $e->getMessage();
        }
        return $result;
    }

    /**
     * @param int $productId
     * @return array
     */
    public function discountCheck(int $productId): array
    {
        $result = ["isSuccess" => false, "message" => "No action", "data" => null];
        try {
            // Ürün indirimli mi?
            $discountCheckQuery = $this->createQueryBuilder('p')
                ->select('p.id')
                ->leftJoin('p.category', 'c')
                ->where('p.id=:id')
                ->setParameter('id', $productId)
                ->andWhere('c.hasCampaign = true')
                ->getQuery()->getArrayResult();
            if (count($discountCheckQuery) > 0) {
                $result["isSuccess"] = true;
                $result["message"] = "Product has campaign";
            } else {
                $result["message"] = "Product has no campaign";
            }
        } catch (\Exception $e) {
            $result["message"] = $e->getMessage();
        }
        return $result;
    }
}
