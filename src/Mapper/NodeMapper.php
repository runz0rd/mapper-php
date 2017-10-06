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
     */
    protected function mapModel($element, $model) {
        $modelReflection = new ModelReflection($model);
        foreach ($modelReflection->getPropertyNames() as $propertyName) {
            $nameAnnotation = $modelReflection->getPropertyAnnotation($propertyName, Name::class);
            $nodeName = $propertyName;
            if($nameAnnotation != null) {
                $nodeName = $nameAnnotation->name;
            }

            $annotations = $modelReflection->getPropertyAnnotations($propertyName);
            $child = $element->getChild($nodeName);
            $value = null;
            if ($child != null) {
                $value = $child->getValue();
            }
            foreach ($annotations as $annotation) {
                if ($annotation instanceof Xml && $annotation->type == Xml::TYPE_ATTRIBUTE && $element->getAttribute($nodeName) != null) {
                    $value = $element->getAttribute($nodeName)->getValue();
                } elseif ($annotation instanceof Xml && $annotation->type == Xml::TYPE_VALUE) {
                    $value = $element->getValue();
                } elseif ($annotation instanceof Type && $child != null) {
                    $className = null;
                    if($annotation->isModel) {
                        $className = $modelReflection->getFullClassName($annotation->annotatedType);
                    }
                    $value = $this->mapPropertyByType($child, $annotation, $className);
                }
            }
            if(isset($value)) {
                $modelReflection->setProperty($propertyName, $value);
            }

        }
        return $model;
    }

    /**
     * @param Node $node
     * @param Type $type
     * @return mixed
     */
    protected function mapPropertyByType($node, $type, $className = null) {
        switch($type->type) {
            case Type::ARRAY:
                $mappedPropertyValue = $this->mapArray($node, $type, $className);
                break;
            case Type::OBJECT:
                $mappedPropertyValue = $this->mapObject($node, $type, $className);
                break;
            default:
                $mappedPropertyValue = $node->getValue();
        }
        return $mappedPropertyValue;
    }

    /**
     * @param Node $node
     * @param Type $type
     * @param string $className
     * @return mixed
     */
    protected function mapObject($node, $type, $className = null) {
        $object = $node->getValue();
        if($type->isModel && $node instanceof ElementNode) {
            $model = ModelReflection::instantiate($className);
            $object = $this->mapModel($node, $model);
        }
        return $object;
    }

    /**
     * @param Node $node
     * @param Type $type
     * @param string $className
     * @return array|mixed
     */
    protected function mapArray($node, $type, $className = null) {
        $nodes = [$node];
        if($node instanceof NodeList) {
            $nodes = $node->getNodes();
        }

        $array = [];
        foreach($nodes as $node) {
            $array[] = $this->mapObject($node, $type, $className);
        }
        return $array;
    }

//    protected function unmapModel($model) {
//        if(!is_object($model) || Validation::isEmpty($model)) {
//            throw new \InvalidArgumentException('Model must be an object with properties.');
//        }
//
//        $modelClass = new ModelClass($model);
//        $node = new \Node\ElementNode();
//        foreach($modelClass->getProperties() as $property) {
//            if($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_ATTRIBUTE)) {
//                $node->addAttribute($property->getPropertyValue());
//            }
//            elseif($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_NODE_VALUE)) {
//                $value = $element->getValue();
//            }
//            else {
//                $value = $property->getPropertyValue();
//                if($element->hasChild($property->getName())) {
//                    $node = $element->getChild($property->getName());
//                    $value = $this->mapPropertyByType($node, $property->getType());
//                }
//            }
//            $property->setPropertyValue($value);
//        }
//
//        return $model;
//    }
}