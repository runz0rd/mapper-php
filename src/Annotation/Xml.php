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
class Xml
{
    const TYPE_ATTRIBUTE = 'attribute';
    const TYPE_VALUE = 'value';

    /**
     * @var string
     */
    public $type;

    public function __construct($values) {
        if(empty($values) || !isset($values['value'])) {
            throw new \Exception('You must specify a xml property type.');
        }
        switch ($values['value']) {
            case self::TYPE_ATTRIBUTE:
            case self::TYPE_VALUE:
                break;
            default:
                throw new \Exception('You must specify a valid (attribute or value) xml property type.');
        }
    }
}