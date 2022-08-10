<?php

namespace App\DBAL;

use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\MysqlJsonFunctionNode;

/**
 * "JSON_OBJECT" "(" { string "," NewValue }* ")"
 */
class JsonObject extends MysqlJsonFunctionNode
{
    const FUNCTION_NAME = 'JSON_OBJECT';

    /** @var string[] */
    protected $optionalArgumentTypes = [self::VALUE_ARG, self::VALUE_ARG];

    /** @var bool */
    protected $allowOptionalArgumentRepeat = true;
}
