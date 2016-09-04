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

class BooleanRule implements IRule {

    function getNames() {
        return ['bool', 'boolean'];
    }

    function validate(ModelProperty $property, array $params = []) {
        Validation::validateBoolean($property->getPropertyValue());
    }
}