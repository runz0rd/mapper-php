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
use Node\Node;
use Node\NodeList;

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
                $value = $element->getAttribute($property->getName());
            }
            elseif($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_NODE_VALUE)) {
                $value = $element->getValue();
            }
            else {
                $value = $property->getPropertyValue();
                if($element->hasChild($property->getName())) {
                    $node = $element->getChild($property->getName());
                    $value = $this->mapPropertyByType($node, $property->getType());
                }
            }
            $property->setPropertyValue($value);
        }

        return $model;
    }

    /**
     * @param Node $node
     * @param ModelPropertyType $modelPropertyType
     * @return mixed
     */
    protected function mapPropertyByType($node, $modelPropertyType) {
        switch($modelPropertyType->getActualType()) {
            case TypeEnum::ARR:
                $mappedPropertyValue = $this->mapArray($node, $modelPropertyType);
                break;
            case TypeEnum::OBJECT:
                $mappedPropertyValue = $this->mapObject($node, $modelPropertyType);
                break;
            default:
                $mappedPropertyValue = $node->getValue();
        }
        return $mappedPropertyValue;
    }

    /**
     * @param Node $node
     * @param ModelPropertyType $modelPropertyType
     * @return mixed
     */
    protected function mapObject($node, $modelPropertyType) {
        $object = $node->getValue();
        if($modelPropertyType->isModel() && $node instanceof Element) {
            $model = ModelClass::instantiate($modelPropertyType->getModelClassName());
            $object = $this->mapModel($node, $model);
        }
        return $object;
    }

    /**
     * @param Node $node
     * @param ModelPropertyType $modelPropertyType
     * @return array|mixed
     */
    protected function mapArray($node, $modelPropertyType) {
        $nodes = [$node];
        if($node instanceof NodeList) {
            $nodes = $node->getNodes();
        }
        $array = [];
        foreach($nodes as $node) {
            $array[] = $this->mapObject($node, $modelPropertyType);
        }
        return $array;
    }

    protected function unmapModel($model) {
        if(!is_object($model) || Validation::isEmpty($model)) {
            throw new \InvalidArgumentException('Model must be an object with properties.');
        }

        $modelClass = new ModelClass($model);
        $node = new \Node\Element();
        foreach($modelClass->getProperties() as $property) {
            if($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_ATTRIBUTE)) {
                $node->addAttribute($property->getPropertyValue());
            }
            elseif($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_NODE_VALUE)) {
                $value = $element->getValue();
            }
            else {
                $value = $property->getPropertyValue();
                if($element->hasChild($property->getName())) {
                    $node = $element->getChild($property->getName());
                    $value = $this->mapPropertyByType($node, $property->getType());
                }
            }
            $property->setPropertyValue($value);
        }

        return $model;
    }
}