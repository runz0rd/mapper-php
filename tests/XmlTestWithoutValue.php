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

class XmlTestWithoutValue {
    use MappableTrait;
    use ConvertibleTrait;
    use ValidatableTrait;

    /**
     * @xmlAttribute
     */
    public $attributeTest;
}