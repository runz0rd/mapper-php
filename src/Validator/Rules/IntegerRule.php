<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 8/20/2016
 * Time: 10:10 AM
 */

namespace Validator\Rules;
use Common\ModelReflection\ModelProperty;
use Common\Util\Validation;
use Validator\IRule;

class IntegerRule implements IRule {

    function getNames() {
        return array('int', 'integer');
    }

    function validate(ModelProperty $property, array $params = array()) {
        Validation::validateInteger($property->getPropertyValue());
    }
}