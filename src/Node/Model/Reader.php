<?php

namespace Node\Model;
use Node\ElementNode;
use Node\IReader;
use Node\TextNode;
use Node\NodeList;

class Reader implements IReader {

    /**
     * @param object $data
     * @return ElementNode
     * @throws \Exception
     */
    public function read($data) {
        return $this->unmapModel($data);
    }

    protected function unmapModel($model) {
        if(!is_object($model) || Validation::isEmpty($model)) {
            throw new \InvalidArgumentException('Model must be an object with properties.');
        }

        $modelClass = new ModelClass($model);
        $element = new ElementNode();
        foreach($modelClass->getProperties() as $property) {
            if($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_ATTRIBUTE)) {
                $element->addAttribute($property->getPropertyValue());
            }
            elseif($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_NODE_VALUE)) {
                $element->setValue($property->getPropertyValue());
            }
            else {
                $this->unmapByType($property, $element);
            }
        }
        return $element;
    }

    protected function unmapByType(\Common\ModelReflection\ModelProperty $property, ElementNode $element) {
        switch($property->getType()->getActualType()) {

        }
    }
}
