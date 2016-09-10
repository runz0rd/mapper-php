<?php

/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 7/31/2016
 * Time: 9:49 AM
 */
use Traits\MappableTrait;
use Traits\ConvertibleTrait;
use Traits\ValidatableTrait;

/**
 * @xmlRoot testModel
 * Class TestModel
 * multi-line comment test
 */
class TestModel {
    use MappableTrait;
    use ConvertibleTrait;
    use ValidatableTrait;

    /**
     * @var
     */
    public $noType;

    /**
     * @var boolean
     */
    public $boolTrue;

    /**
     * @var boolean
     */
    public $boolFalse;

    /**
     * @var string
     */
    public $string;

    /**
     * @name namedString123
     * @var string
     */
    public $namedString;

    /**
     * testing the multiline comments
     * right here
     * @var integer
     */
    public $integer;

    /**
     * @var array
     */
    public $array;

    /**
     * @var string[]
     */
    public $stringArray;

    /**
     * @var integer[]
     */
    public $integerArray;

    /**
     * @var boolean[]
     */
    public $booleanArray;

    /**
     * @var object[]
     */
    public $objectArray;

    /**
     * @var object
     */
    public $object;

    /**
     * @var NestedTestModel
     */
    public $model;

    /**
     * @var NestedTestModel[]
     */
    public $modelArray;

    /**
     * @required requiredString
     * @required testRequired
     * @var string
     */
    public $requiredString;

    /**
     * @required
     * @var boolean
     */
    public $alwaysRequiredBoolean;

    /**
     * @required requiredInteger
     * @required testRequired
     * @var integer
     */
    public $multipleRequiredInteger;

    /**
     * @xmlAttribute
     * @var string
     */
    public $attribute1;

    /**
     * @rule email(10, 99, asdf)
     */
    public $emailRule;

    /**
     * @rule string
     * @rule IP
     */
    public $multipleRules;

    /**
     * @var XmlTestModel
     */
    public $xml;
}