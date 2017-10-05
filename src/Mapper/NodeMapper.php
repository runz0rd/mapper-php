<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 3/29/2016
 * Time: 9:27 AM
 */

namespace Mapper;
use Annotation\Name;
use Annotation\Type;
use Annotation\Xml;
use Common\ModelReflection\Enum\AnnotationEnum;
use Common\ModelReflection\Enum\TypeEnum;
use Common\ModelReflection\ModelClass;
use Common\ModelReflection\ModelPropertyType;
use Common\Util\Validation;
use Node\ElementNode;
use Node\Node;
use Node\NodeList;
use Reflection\ModelReflection;

class NodeMapper implements IModelMapper {

	/**
	 * @param ElementNode $node
	 * @param object $model
	 * @return object
	 */
	public function map($node, $model) {
        if(!$node instanceof ElementNode) {
            throw new \InvalidArgumentException('Source must be an instance of ' . ElementNode::class);
        }
        if(!is_object($model) || Validation::isEmpty($model)) {
            throw new \InvalidArgumentException('Model must be an object with properties.');
        }
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
     * @param ElementNode $element
     * @param object $model
     * @return object
     * @throws \InvalidArgumentException
     */
	protected function mapModel($element, $model) {
        if(!$element instanceof ElementNode) {
            throw new \InvalidArgumentException('Source must be an instance of ' . ElementNode::class);
        }
        if(!is_object($model) || Validation::isEmpty($model)) {
            throw new \InvalidArgumentException('Model must be an object with properties.');
        }

        $modelClass = new ModelClass($model);
        foreach($modelClass->getProperties() as $property) {
            $value = $property->getPropertyValue();
            if($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_ATTRIBUTE) && $element->hasAttribute($property->getName())) {
                $value = $element->getAttribute($property->getName());
            }
            elseif($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_NODE_VALUE)) {
                $value = $element->getValue();
            }
            elseif($element->hasChild($property->getName())) {
                $node = $element->getChild($property->getName());
                $value = $this->mapPropertyByType($node, $property->getType());
            }
            $property->setPropertyValue($value);
        }

        return $model;
    }

    /**
     * @param ElementNode $element
     * @param object $model
     * @return object
     */
    protected function _mapModel($element, $model) {
        $modelReflection = new ModelReflection($model);
        foreach ($modelReflection->getPropertyNames() as $propertyName) {
            $nameAnnotation = $modelReflection->getPropertyAnnotation($propertyName, Name::class);
            $nodeName = $this->getNodeName($propertyName, $nameAnnotation);
            $value = $element->getChild($nodeName)->getValue();
            $annotations = $modelReflection->getPropertyAnnotations($propertyName);
            foreach ($annotations as $annotation) {
                if($annotation instanceof Xml && $annotation->type == Xml::TYPE_ATTRIBUTE) {
                    $value = $element->getAttribute($nodeName);
                }
                if($annotation instanceof Xml && $annotation->type == Xml::TYPE_VALUE) {
                    $value = $element->getValue();
                }
                if($annotation instanceof Type) {
                    
                }
            }
            $modelReflection->setProperty($propertyName, $value);
        }
        return $model;
    }

    /**
     * @param string $propertyName
     * @param Name $nameAnnotation
     * @return mixed
     */
    protected function getNodeName($propertyName, $nameAnnotation) {
        $name = $propertyName;
        if($nameAnnotation!= null) {
            $name = $nameAnnotation->name;
        }
        return $name;
    }

    /**
     * @param Node $node
     * @param string $type
     * @param string $annotatedType
     * @return mixed
     */
    protected function mapPropertyByType($node, $type, $annotatedType) {
        switch($type) {
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
    protected function mapObject($node, $type, $annotatedType) {
        $object = $node->getValue();
        if($modelPropertyType->isModel() && $node instanceof ElementNode) {
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
    protected function mapArray($node, $type, $annotatedType) {
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
        $node = new \Node\ElementNode();
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