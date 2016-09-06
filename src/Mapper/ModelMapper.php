<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 3/29/2016
 * Time: 9:27 AM
 */

namespace Mapper;
use Common\ModelReflection\Enum\AnnotationEnum;
use Common\ModelReflection\Enum\TypeEnum;
use Common\ModelReflection\ModelClass;
use Common\ModelReflection\ModelPropertyType;
use Common\Util\Validation;
use Common\Util\Iteration;

class ModelMapper implements IModelMapper {

    const ATTR_KEY = '@attributes';

	/**
	 * @param object $source
	 * @param object $model
	 * @return object
	 * @throws \InvalidArgumentException
	 */
	public function map($source, $model) {
		if(!is_object($source) || Validation::isEmpty($source)) {
			throw new \InvalidArgumentException('Source must be an object with properties.');
		}
        if(!is_object($model) || Validation::isEmpty($model)) {
            throw new \InvalidArgumentException('Model must be an object with properties.');
        }
		$modelClass = new ModelClass($model);

		foreach($modelClass->getProperties() as $property) {
		    if($property->getDocBlock()->hasAnnotation(AnnotationEnum::ATTRIBUTE)) {
                $mappedValue = $this->mapAttributeByName($property->getName(), $source);
            }
            else {
                $sourceValue = Iteration::findValueByName($property->getName(), $source, $property->getPropertyValue());
                $mappedValue = $this->mapPropertyByType($property->getType(), $sourceValue);

            }
            $property->setPropertyValue($mappedValue);
		}

		return $model;
	}

    protected function mapAttributeByName(string $attributeName, $source) {
        $mappedAttributeValue = null;
        $attributesKey = self::ATTR_KEY;
        if(isset($source->$attributesKey) && isset($source->$attributesKey[$attributeName])) {
            $mappedAttributeValue = $source->$attributesKey[$attributeName];
        }

        return $mappedAttributeValue;
    }

	/**
	 * @param ModelPropertyType $propertyType
	 * @param mixed $value
	 * @return mixed
	 */
	protected function mapPropertyByType(ModelPropertyType $propertyType, $value) {
        $mappedPropertyValue = $value;
		if($propertyType->isModel()) {
			if($propertyType->getActualType() === TypeEnum::ARRAY && is_array($value)) {
				$mappedPropertyValue = $this->mapModelArray($propertyType->getModelClassName(), $value);
			}
			elseif($propertyType->getActualType() === TypeEnum::OBJECT && is_object($value)) {
				$mappedPropertyValue = $this->mapModel($propertyType->getModelClassName(), $value);
			}
		}

		return $mappedPropertyValue;
	}

	/**
	 * @param string $modelClassName
	 * @param array $source
	 * @return array
	 */
	protected function mapModelArray(string $modelClassName, array $source) {
		$mappedModelArray = null;
		foreach($source as $key => $value) {
//			$mappedModelArray[$key] = $value;
			if(is_object($value)) {
				$mappedModelArray[$key] = $this->mapModel($modelClassName, $value);
			}
		}

		return $mappedModelArray;
	}

	/**
	 * @param string $modelClassName
	 * @param object $source
	 * @return object
	 */
    protected function mapModel(string $modelClassName, $source) {
		$model = new $modelClassName();
		$mappedModel = self::map($source, $model);

		return $mappedModel;
	}

	/**
	 * @param object $model
	 * @return \stdClass
	 * @throws \InvalidArgumentException
	 */
	public function unmap($model) {
		if(!is_object($model) || Validation::isEmpty($model)) {
			throw new \InvalidArgumentException('Model must be an object with properties.');
		}

		$modelClass = new ModelClass($model);
		$unmappedObject = new \stdClass();
		foreach($modelClass->getProperties() as $property) {
			$propertyKey = $property->getName();
			$propertyValue = $property->getPropertyValue();
			if (Validation::isEmpty($propertyValue)) {
				continue;
			}
			if($property->getDocBlock()->hasAnnotation(AnnotationEnum::ATTRIBUTE)) {
				$attributeKey = self::ATTR_KEY;
				$unmappedObject->$attributeKey = new \stdClass();
				$unmappedObject->$attributeKey->$propertyKey = $propertyValue;
			}
			else {
				$unmappedObject->$propertyKey = $this->unmapValueByType($property->getType(), $propertyValue);
			}
		}

		return $unmappedObject;
	}

	/**
	 * @param ModelPropertyType $propertyType
	 * @param mixed $value
	 * @return mixed
	 */
	protected function unmapValueByType(ModelPropertyType $propertyType, $value) {
		$unmappedPropertyValue = $value;

		if($propertyType->isModel()) {
			if($propertyType->getActualType() === TypeEnum::ARRAY && is_array($value)) {
				$unmappedPropertyValue = $this->unmapModelArray($value);
			}

			elseif($propertyType->getActualType() === TypeEnum::OBJECT && is_object($value)) {
				$unmappedPropertyValue = $this->unmapModel($value);
			}
		}

		return $unmappedPropertyValue;
	}

	/**
	 * @param array $modelArray
	 * @return array
	 */
	protected function unmapModelArray(array $modelArray) {
		$unmappedObjectArray = [];
		foreach($modelArray as $k => $v) {
			$unmappedObjectArray[$k] = $this->unmapModel($v);
		}

		return $unmappedObjectArray;
	}

	/**
	 * @param object $model
	 * @return object
	 */
	protected function unmapModel($model) {
		$unmappedObject = self::unmap($model);

		return $unmappedObject;
	}

	/**
	 * @param $object
	 * @param string $rootName
	 * @return \stdClass
	 */
	protected function addRootElement($object, string $rootName) {
		$newObject = new \stdClass();
		$newObject->$rootName = $object;

		return $newObject;
	}

    /**
     * @param object $model
     * @return string
     */
    public function getModelRootName($model) {
        $modelClass = new ModelClass($model);
        $rootName = $modelClass->getRootName();

        return $rootName;
    }
}