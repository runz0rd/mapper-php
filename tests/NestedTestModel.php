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
class NestedTestModel {
    use MappableTrait;
    use ConvertibleTrait;
    use ValidatableTrait;

    /**
     * @var
     */
    public $noType;

    /**
     * @Annotation\Type("boolean")
     * @var boolean
     */
    public $boolTrue;

    /**
     * @Annotation\Type("bool")
     * @var boolean
     */
    public $boolFalse;

    /**
     * @Annotation\Type("string")
     * @var string
     */
    public $string;

    /**
     * @var string
     */
    public $namedString;

    /**
     * testing the multiline comments
     * right here
     * @Annotation\Type("integer")
     * @var integer
     */
    public $integer;

    /**
     * @Annotation\Type("array")
     * @var array
     */
    public $array;

    /**
     * @Annotation\Type("array")
     * @var string[]
     */
    public $stringArray;

    /**
     * @Annotation\Type("array")
     * @var integer[]
     */
    public $integerArray;

    /**
     * @Annotation\Type("array")
     * @var boolean[]
     */
    public $booleanArray;

    /**
     * @Annotation\Type("array")
     * @var object[]
     */
    public $objectArray;

    /**
     * @Annotation\Type("object")
     * @var object
     */
    public $object;

    /**
     * @Annotation\Type("string")
     * @required requiredString
     * @required testRequired
     * @var string
     */
    public $requiredString;

    /**
     * @Annotation\Type("boolean")
     * @required
     * @var boolean
     */
    public $alwaysRequiredBoolean;

    /**
     * @Annotation\Type("int")
     * @required requiredInteger
     * @required testRequired
     * @var integer
     */
    public $multipleRequiredInteger;

    /**
     * @Annotation\Type("string")
     * @Annotation\Xml("attribute")
     * @var string
     */
    public $attribute1;
}