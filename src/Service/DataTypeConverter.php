<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class DataTypeConverter
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $data
     * @return string
     */
    public function toJsonString($data)
    {
        if (is_object($data) || is_array($data)) {
            return $this->container->get('serializer')->serialize($data, 'json');
        }
        return "[]";
    }


    /**
     * @param array $data
     * @param $index
     * @param $column
     * @return string
     */
    public function vueSelect2DataToJsonString( $index, $column,array $data = [])
    {
        try {
            $res = [];
            if (count($data) > 0) {
                foreach ($data as $item) {
                    $res[] = [
                        "id" => $item[$index],
                        "text" => $item[$column]
                    ];
                }
            }
            return $this->toJsonString($res);
        } catch (\Exception $exception) {
           return "[]";
        }
    }

    /**
     * @param array $data
     * @param $index
     * @param $column
     * @return array
     */
    public function vueSelect2DataToJson( $index, $column,array $data = [])
    {
        try {
            $res = [];
            if (count($data) > 0) {
                foreach ($data as $item) {
                    $res[] = [
                        "id" => $item[$index],
                        "text" => $item[$column]
                    ];
                }
            }
            return $res;
        } catch (\Exception $exception) {
            return [];
        }
    }
}
