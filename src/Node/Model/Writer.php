<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 11/03/17
 * Time: 21:57
 */

namespace Node\Model;
use Node\ElementNode;
use Node\TextNode;
use Node\IWriter;
use Node\NodeList;

class Writer implements IWriter {

    /**
     * @var object
     */
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function write($node) {
        return $this->mapModel($node, $this->model);
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
        if(!is_object($model) || \Common\Util\Validation::isEmpty($model)) {
            throw new \InvalidArgumentException('Model must be an object with properties.');
        }

        $modelClass = new \Common\ModelReflection\ModelClass($model);
        foreach($modelClass->getProperties() as $property) {
            $value = $property->getPropertyValue();
            if($property->getDocBlock()->hasAnnotation(\Common\ModelReflection\Enum\AnnotationEnum::XML_ATTRIBUTE)
                && $element->hasAttribute($property->getName())) {
                $value = $element->getAttribute($property->getName());
            }
            elseif($property->getDocBlock()->hasAnnotation(\Common\ModelReflection\Enum\AnnotationEnum::XML_NODE_VALUE)) {
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
     * @param TextNode $node
     * @param ModelPropertyType $modelPropertyType
     * @return mixed
     */
    protected function mapPropertyByType($node, $modelPropertyType) {
        switch($modelPropertyType->getActualType()) {
            case \Common\ModelReflection\Enum\TypeEnum::ARR:
                $mappedPropertyValue = $this->mapArray($node, $modelPropertyType);
                break;
            case \Common\ModelReflection\Enum\TypeEnum::OBJECT:
                $mappedPropertyValue = $this->mapObject($node, $modelPropertyType);
                break;
            default:
                $mappedPropertyValue = $node->getValue();
        }
        return $mappedPropertyValue;
    }

    /**
     * @param TextNode $node
     * @param ModelPropertyType $modelPropertyType
     * @return mixed
     */
    protected function mapObject($node, $modelPropertyType) {
        $object = $node->getValue();
        if($modelPropertyType->isModel() && $node instanceof ElementNode) {
            $model = \Common\ModelReflection\ModelClass::instantiate($modelPropertyType->getModelClassName());
            $object = $this->mapModel($node, $model);
        }
        return $object;
    }

    /**
     * @param TextNode $node
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

    /**
     * @param TextNode $node
     */
    protected function writeNode($node) {
        if($node instanceof ElementNode) {
            $this->writer->startElement($node->getName());
            $this->writeAttributes($node);
            $this->writeElement($node);
            $this->writer->fullEndElement();
        }
        elseif($node instanceof NodeList) {
            $this->writeNodeList($node);
        }
        else {
            $this->writer->startElement($node->getName());
            $this->writeAttributes($node);
            $this->writer->text((string) $node->getValue());
            $this->writer->fullEndElement();
        }
    }

    /**
     * @param TextNode $node
     */
    protected function writeAttributes($node) {
        foreach ($node->getAttributes() as $attributeKey => $attributeValue) {
            $this->writer->writeAttribute($attributeKey, $attributeValue);
        }
    }

    /**
     * @param ElementNode $node
     */
    protected function writeElement($node) {
        foreach ($node->getChildren() as $child) {
            $this->writeNode($child);
        }
        if(empty($node->getChildren())) {
            $stringValue = $this->toString($node->getValue());
            $this->writer->text($stringValue);
        }
    }

    /**
     * @param NodeList $node
     */
    protected function writeNodeList($node) {
        foreach ($node->getNodes() as $node) {
            $this->writeNode($node);
        }
    }

    protected function toString($value) {
        $result = (string) $value;
        if(is_bool($value)) {
            $result = 'false';
            if($value) {
                $result = 'true';
            }
        }
        return $result;
    }
}