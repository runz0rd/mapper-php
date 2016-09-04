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

class IpRule implements IRule {

    function getNames() {
        return ['IP'];
    }

    function validate(ModelProperty $property, array $params = []) {
        if(filter_var($property->getPropertyValue(), FILTER_VALIDATE_IP) === false) {
            throw new ModelValidatorException('Value is not an IP.');
        }
    }
}