<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 8/20/2016
 * Time: 10:10 AM
 */

namespace Validator\Rules;
use Common\ModelReflection\ModelProperty;
use Validator\IRule;
use Validator\ModelValidatorException;

class EmailRule implements IRule {

    function getNames() {
        return array('email');
    }

    function validate(ModelProperty $property, array $params = array()) {
        if(filter_var($property->getPropertyValue(), FILTER_VALIDATE_EMAIL) === false) {
            throw new ModelValidatorException('Value is not a valid email.');
        }
    }
}