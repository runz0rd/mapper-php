<?php

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 29/05/17
 * Time: 21:51
 */
namespace Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Type
{

    const INT = 'int';
    const INTEGER = 'integer';
    const STRING = 'string';
    const BOOL = 'bool';
    const BOOLEAN = 'boolean';
    const DOUBLE = 'double';
    const OBJECT = 'object';
    const ARRAY = 'array';

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $annotatedType;

    /**
     * @var bool
     */
    public $isModel = false;

    public function __construct($values) {
        if(empty($values) || !isset($values['value'])) {
            throw new \Exception('You must specify a type.');
        }
        switch ($values['value']) {
            case self::INT:
            case self::INTEGER:
            case self::STRING:
            case self::BOOL:
            case self::BOOLEAN:
            case self::DOUBLE:
            case self::OBJECT:
            case self::ARRAY:
                $this->annotatedType = $values['value'];
                $this->type = $values['value'];
                break;
            default:
                $this->annotatedType = $values['value'];
                $this->type = 'object';
                if(strpos($values['value'], '[]') !== false) {
                    $this->annotatedType = rtrim($values['value'], '[]');
                    $this->type = 'array';
                }
                $this->isModel = true;
        }
    }
}