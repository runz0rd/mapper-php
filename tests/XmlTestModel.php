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

class XmlTestModel {
    use MappableTrait;
    use ConvertibleTrait;
    use ValidatableTrait;

    /**
     * @Annotation\Name("xmlns:test")
     * @Annotation\Xml("attribute")
     * @name xmlns:test
     * @xmlAttribute
     */
    public $ns;

    /**
     * @Annotation\Xml("attribute")
     * @xmlAttribute
     */
    public $attributeTest;

    /**
     * @Annotation\Xml("value")
     * @xmlNodeValue
     */
    public $value;
}