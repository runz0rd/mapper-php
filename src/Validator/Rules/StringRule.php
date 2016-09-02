<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 8/20/2016
 * Time: 10:10 AM
 */

namespace Validator\Rules;
use Common\Models\ModelProperty;
use Common\Util\Validation;
use Validator\IRule;

class StringRule implements IRule {

    function getNames() {
        return ['string'];
    }

    function validate(ModelProperty $property, array $params = []) {
        Validation::validateString($property->getPropertyValue());
    }
}