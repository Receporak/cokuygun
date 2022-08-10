<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Symfony\Component\String\s;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function add(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param array $postData
     * @param Category $category
     * @return array
     */
    public function newCategory(array $postData,Category $category):array
    {
        $result = ["isSuccess" => false, "message" => "No action", "data" => null];
        try {
            $level = 1;
            if (isset($postData["parent"]) && $postData["parent"] != "") {
                $level=$category->getParent()->getLevel()+1;
            }
            $category->setLevel($level);
            $this->add($category, true);

            $result["isSuccess"] = true;
            $result["data"] = $category;
        } catch (\Exception $e) {
            $result["isSuccess"] = false;
            $result["message"] = $e->getMessage();
        }
        return $result;
    }

    /**
     * @param array $postData
     * @return array
     */
    public function getCategories(array $postData): array
    {
        $result = ["isSuccess" => true, "message" => "No action", "data" => null];

        try {
            $categories = $this->createQueryBuilder("c");
            $categories->select("c.id, c.name");
            $categories
                ->leftJoin("c.products", "p");
            $categories
                ->where("p.id IS NOT NULL");

            if (isset($postData["id"])) {
                $categories
                    ->where("c.parent =:id")
                    ->setParameter("id", $postData["id"]);
            } else {
                $categories->where("c.parent IS NULL");
            }
            $categories = $categories
                ->groupBy("c.id")
                ->getQuery()
                ->getResult();
            $result["data"] = $categories;
        } catch (\Exception $e) {
            $result["isSuccess"] = false;
            $result["message"] = $e->getMessage();
        }
        return $result;
    }


    /**
     * @param array $postData
     * @return array
     */
    public function getAllCategoryForSelect(array $postData): array
    {
        $result = ["isSuccess" => false, "message" => "No action taken", "errorMessage" => null, "data" => [],"formTypeData"=>[]];
        $em = $this->getEntityManager();
        try {
            // Tüm ana kategorileri çekiyoruz.
            $mainCategories = $this->createQueryBuilder("c");
            $mainCategories
                ->select('c.id',"c.name as label")
                ->where("c.parent IS NULL");
            $mainCategories = $mainCategories->getQuery()->getArrayResult();

            // Sıralı ve bağlantılı bir şekilde getirmek için kategorileri çekiyoruz.
            $query = $this->createQueryBuilder("c");
            $query
                ->select('c.id',"c.name as label","c.orderNo");

            $res = $query->getQuery()->getArrayResult();
            $resCount = count($res);
            $rawQueryString = "";

            // Tüm kategorileri ana kategorilerine bağlı olarak sıralı bir şekilde getiriyoruz.
            if ($resCount > 0) {
                $rawQueryString = "
                    SELECT
                        CONCAT(
                            '[',
                ";
                foreach ($res as $key => $item) {
                    if ($key == ($resCount - 1)) {
                        $rawQueryString .= "
                            '{',
                            '\"id\":" . $item['id'] . "',
                            ',',
                             '\"orderNo\":" . $item['orderNo'] . "',
                            ',',
                            '\"label\":\"',
                                (SELECT
                                    GROUP_CONCAT(T2_" . $key . ".name SEPARATOR ' > ')
                                FROM
                                    (SELECT
                                        @r_" . $key . " AS _id_" . $key . ",
                                        @p_" . $key . " := @r_" . $key . " AS previous_" . $key . ",
                                        (SELECT @r_" . $key . " := parent_id FROM category WHERE id = _id_" . $key . ") AS parent_id_" . $key . ",
                                        @l_" . $key . " := @l_" . $key . " + 1 AS lvl_" . $key . "
                                    FROM (SELECT @r_" . $key . " := " . $item['id'] . ", @p_" . $key . " := 0, @l_" . $key . " := 0) vars, category h_" . $key . "
                                    WHERE @r_" . $key . " <> 0
                                    AND @r_" . $key . " <> @p_" . $key . "
                                    ORDER BY @l_" . $key . " DESC) T1_" . $key . "
                                LEFT JOIN category T2_" . $key . "
                                ON T1_" . $key . "._id_" . $key . " = T2_" . $key . ".id
                                )
                                ,
                            '\"}'
                        ";
                    } else {
                        $rawQueryString .= "
                            '{',
                            '\"id\":" . $item['id'] . "',
                            ',',  
                             '\"orderNo\":" . $item['orderNo'] . "',
                            ',',
                            '\"label\":\"',
                                (SELECT
                                    GROUP_CONCAT(T2_" . $key . ".name SEPARATOR ' > ')
                                FROM
                                    (SELECT
                                        @r_" . $key . " AS _id_" . $key . ",
                                        @p_" . $key . " := @r_" . $key . " AS previous_" . $key . ",
                                        (SELECT @r_" . $key . " := parent_id FROM category WHERE id = _id_" . $key . "  ) AS parent_id_" . $key . ",
                                        @l_" . $key . " := @l_" . $key . " + 1 AS lvl_" . $key . "
                                    FROM (SELECT @r_" . $key . " := " . $item['id'] . ", @p_" . $key . " := 0, @l_" . $key . " := 0) vars, category h_" . $key . "
                                    WHERE @r_" . $key . " <> 0
                                    AND @r_" . $key . " <> @p_" . $key . "
                                    ORDER BY @l_" . $key . " DESC) T1_" . $key . "
                                LEFT JOIN category T2_" . $key . "
                                ON T1_" . $key . "._id_" . $key . " = T2_" . $key . ".id
                                ),
                            '\"}',
                            ',',
                        ";
                    }
                }
                $rawQueryString .= "
                            ']'
                        ) AS categoryList
                    FROM category cat
                    LIMIT 1
                ";
            }
            if ($rawQueryString != "") {
                $categoryQueryResult = $em->getConnection()->executeQuery($rawQueryString)->fetchAllAssociative();
                $categoryQueryResult = json_decode($categoryQueryResult[0]["categoryList"], true);
                $orderedData = [];

                // Kategori listesini öncelikleri girilen sıraya göre getirmek için orderNo değerlerine göre sıralıyoruz.
                foreach ($categoryQueryResult as $row) {
                    foreach ($row as $key => $value){
                        if ($key == "orderNo") {
                            $orderedData[]  = $value;
                        }
                    }
                }
                // Kategori listesini sıralıyoruz.
                array_multisort($orderedData, SORT_ASC,$categoryQueryResult);
                $categoryList = [];

                // Ana kategorileri de sıraladığımız kategoriler listesine ekliyoruz.
                foreach ($mainCategories as $data) {
                    $categoryList[$data["label"]] = $this->find($data["id"]);
                }
                foreach ($categoryQueryResult as $data) {
                    $categoryList[$data["label"]] = $this->find($data["id"]);
                }
                // düzenleme işlemlerinde kategoriyi kendisine bağlamaması için düzenlenen kategoriyi listeden kaldırıyoruz.
                if (isset($postData["deniedCategoryId"])){
                    $categoryList= array_filter($categoryList, function ($item) use ($postData) {
                        return $postData["deniedCategoryId"] != $item->getId();
                    });
                }


                $result["isSuccess"] = true;
                $result["message"] = "Success";
                $result["data"] = $categoryQueryResult;
                $result["formTypeData"] = $categoryList;
            } else {
                $result["isSuccess"] = false;
                $result["message"] = "Query String Not Found";
                $result["errorMessage"] = "somethingWentWrong";
                $result["data"] = [];
            }
        } catch (\Exception $exception) {
            $result["isSuccess"] = false;
            $result["message"] = $exception->getMessage();
            $result["errorMessage"] = "somethingWentWrong";
            $result["data"] = [];
        }
        return $result;
    }

    /**
     * @param int $categoryId
     * @return mixed[]|\mixed[][]|null
     */
    public function getMultiMainCategory(int $categoryId): ?array
    {
        try {
            $em = $this->getEntityManager();
            $category = $em->getConnection()->executeQuery('
            SELECT @org_id as id,
                (SELECT id FROM category WHERE id = @org_id) as id,
                (SELECT id FROM category WHERE id = @org_id) as id,
                (SELECT name FROM category WHERE id = @org_id) AS name,
                (SELECT @org_id := parent_id FROM category WHERE id = @org_id) AS parent_id
            FROM (SELECT @org_id := ' . $categoryId . ') vars, category org
            WHERE @org_id is not NULL
            ORDER BY id;
        ')->fetchAll();
            return $category;
        } catch (\Exception $exception) {
            return null;
        }
    }
}
