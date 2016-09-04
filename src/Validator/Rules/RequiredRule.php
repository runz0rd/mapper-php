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
use Validator\ModelValidatorException;

class RequiredRule implements IRule {

    function getNames() {
        return ['required'];
    }

    function validate(ModelProperty $property, array $params = []) {
        if(isset($params[0]) && $property->isRequired()) {
            $requiredAction = $params[0];

            foreach($property->getRequiredActions() as $propertyRequiredAction) {
                if($propertyRequiredAction === $requiredAction || $propertyRequiredAction == '') {
                    if(Validation::isEmpty($property->getPropertyValue())) {
                        throw new ModelValidatorException('Required property cannot be empty.');
                    }
                }
            }
        }
    }
}