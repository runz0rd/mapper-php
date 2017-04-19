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
use Node\Element;

class NodeMapper implements IModelMapper {

	/**
	 * @param Element $node
	 * @param object $model
	 * @return object
	 */
	public function map($node, $model) {
		return $this->mapModel($node, $model);
	}

    /**
     * @param object $model
     * @return \stdClass
     */
    public function unmap($model) {
        return $this->unmapModel($model);
    }

    /**
     * @param Element $element
     * @param object $model
     * @return object
     * @throws \InvalidArgumentException
     */
	protected function mapModel($element, $model) {
        if(!$element instanceof Element) {
            throw new \InvalidArgumentException('Source must be an instance of ' . Element::class);
        }
        if(!is_object($model) || Validation::isEmpty($model)) {
            throw new \InvalidArgumentException('Model must be an object with properties.');
        }
        $modelClass = new ModelClass($model);

        foreach($modelClass->getProperties() as $property) {
            if($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_ATTRIBUTE) && $element->hasAttribute($property->getName())) {
                $value = $element->getAttribute($property->getName())->getValue();
            }
            elseif($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_NODE_VALUE)) {
                $value = $element->getValue();
            }
            else {
                $value = $property->getPropertyValue();
                if($element->hasChild($property->getName())) {
                    $nodes = $element->getChildren($property->getName());
                    $value = $this->mapPropertyByType($nodes, $property->getType());
                }
            }
            $property->setPropertyValue($value);
        }

        return $model;
    }

    /**
     * @param Element[] $nodes
     * @param ModelPropertyType $modelPropertyType
     * @return mixed
     */
    protected function mapPropertyByType($nodes, $modelPropertyType) {
        switch($modelPropertyType->getActualType()) {
            case TypeEnum::ARR:
                $mappedPropertyValue = $this->mapArray($nodes, $modelPropertyType);
                break;
            case TypeEnum::OBJECT:
                $mappedPropertyValue = $this->mapObject($nodes[0], $modelPropertyType);
                break;
            default:
                $mappedPropertyValue = $nodes[0]->getValue();
        }
        return $mappedPropertyValue;
    }

    /**
     * @param Element $node
     * @param ModelPropertyType $modelPropertyType
     * @return mixed
     */
    protected function mapObject($node, $modelPropertyType) {
        switch ($modelPropertyType->isModel()) {
            case true:
                $model = ModelClass::instantiate($modelPropertyType->getModelClassName());
                $object = $this->mapModel($node, $model);
                break;
            default:
                $object = $node->toObject();
        }

        return $object;
    }

    /**
     * @param Element[] $nodes
     * @param ModelPropertyType $modelPropertyType
     * @return array|mixed
     */
    protected function mapArray($nodes, $modelPropertyType) {
        //TODO revise regarding nodeList
        $array = array();
        foreach($nodes as $node) {
            $array[] = $this->mapObject($node, $modelPropertyType);
        }
        return $array;
    }
}