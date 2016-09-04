<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 6/10/2016
 * Time: 4:09 PM
 */

namespace Validator;
use Common\ModelReflection\Enum\AnnotationEnum;
use Common\ModelReflection\ModelClass;
use Common\ModelReflection\ModelProperty;
use Common\Util\Iteration;
use Common\Util\Validation;

class ModelValidator {

    /**
     * @var IRule[]
     */
    private $rules;

	/**
	 * @param object $object
	 * @param string $validationRequiredType
	 * @throws ModelValidatorException
	 * @throws \InvalidArgumentException
	 */
	public function validate($object, string $validationRequiredType = '') {
		if(!is_object($object)) {
			throw new \InvalidArgumentException('Invalid object supplied for validation.');
		}

		$this->loadRules(__DIR__ . '\Rules');

		$modelClass = new ModelClass($object);
		foreach($modelClass->getProperties() as $property) {
			$this->validateProperty($property, $validationRequiredType);
		}
	}

    /**
     * Load all the rule classes from the specified folder
     * @param string $location
     */
	public function loadRules(string $location) {
		$files = glob($location . '\*.php');
        foreach($files as $file) {
            @require_once $file;
	        $filename = basename($file, ".php");
            $autoloaded = get_declared_classes();
            $className = Iteration::regexArray($autoloaded, "/\\\\$filename$/");

            if(!is_null($className)) {
                /** @var IRule $rule */
                $rule = new $className;
                if($rule instanceof IRule) {
                   $this->useRule($rule);
                }
            }
        }
    }

    /**
     * @param IRule $rule
     */
    public function useRule(IRule $rule) {
	    foreach($rule->getNames() as $name) {
		    $this->rules[strtolower($name)] = $rule;
	    }
    }

    /**
     * @param ModelProperty $property
     * @throws ModelValidatorException
     */
    protected function validateRules(ModelProperty $property) {
        if(!is_null($property->getPropertyValue()) && $property->getDocBlock()->hasAnnotation(AnnotationEnum::RULE)) {
            $definedRules = $property->getDocBlock()->getAnnotation(AnnotationEnum::RULE);
            foreach($definedRules as $definedRule) {
	            $ruleName = strtolower($definedRule);
	            $params = [];
				if(preg_match('/(.*)\((.*)\)/', $definedRule, $matches)) {
					$ruleName = trim($matches[1]);
					$params = array_map('trim', explode(",", $matches[2]));
				}
                $this->validateRule($property, $ruleName, $params);
            }
        }
    }

    /**
     * @param ModelProperty $property
     * @param string $ruleName
     * @param array $params
     * @throws ModelValidatorException
     */
	protected function validateRule(ModelProperty $property, string $ruleName, array $params = []) {
		if(isset($this->rules[$ruleName])) {
			$rule = $this->rules[$ruleName];
			try {
				$rule->validate($property, $params);
			}
			catch(\Exception $ex) {
				$message = 'Error while validating ' . $property->getParentClassName() . '::' . $property->getPropertyName() . '. ' . $ex->getMessage();
				throw new ModelValidatorException($message);
			}
		}
	}

	/**
	 * @param ModelProperty $property
	 * @param string $requiredType
	 */
	protected function validateProperty(ModelProperty $property, string $requiredType) {
		if($property->getDocBlock()->hasAnnotation(AnnotationEnum::VAR) && !is_null($property->getPropertyValue())) {
			$this->validateRule($property, $property->getType()->getActualType());
		}
		$this->validateRule($property, 'required', [$requiredType]);
		$this->validateRules($property);

		if($property->getType()->isModel() && !Validation::isEmpty($property->getPropertyValue())) {
			$this->validateModelProperty($property->getPropertyValue(), $requiredType);
		}
	}

	/**
	 * @param array|object $propertyValue
	 * @param string $requiredType
	 */
	protected function validateModelProperty($propertyValue, string $requiredType) {
        if(is_array($propertyValue)) {
            foreach($propertyValue as $value) {
                $this->validate($value, $requiredType);
            }
        }
        if(is_object($propertyValue)) {
            $this->validate($propertyValue, $requiredType);
        }
	}
}