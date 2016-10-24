<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 8/9/2016
 * Time: 7:56 PM
 */

namespace Mapper;
use Common\ModelReflection\Enum\AnnotationEnum;
use Common\ModelReflection\Enum\TypeEnum;
use Common\ModelReflection\ModelClass;
use Common\ModelReflection\ModelPropertyType;
use Common\Util\Iteration;
use Common\Util\Validation;
use Common\Util\Xml;

class XmlModelMapper extends ModelMapper implements IModelMapper {

    const ATTR_KEY = '@attributes';
    const VALUE_KEY = '@value';

    /**
     * @override Converts xml to an object and then maps
     * @param string $source
     * @param object $model
     * @throws ModelMapperException
     * @return object
     */
    public function map($source, $model) {
        $xml = Xml::removeWhitespace($source);
        $domDocument = new \DOMDocument();
        $xmlLoadSuccess = $domDocument->loadXML($xml);
        if(!$xmlLoadSuccess) {
            throw new ModelMapperException('Invalid xml provided.');
        }
        $domElement = $domDocument->documentElement;
        $source = $this->domNodeToObject($domElement);
        $mappedModel = $this->mapModel($source, $model);

        return $mappedModel;
    }

    /**
     * @override Maps xml attributes and node values accordingly
     * @param object $source
     * @param object $model
     * @return object
     */
    protected function mapModel($source, $model) {
        if(!is_object($source) || Validation::isEmpty($source)) {
            throw new \InvalidArgumentException('Source must be an object with properties.');
        }
        if(!is_object($model) || Validation::isEmpty($model)) {
            throw new \InvalidArgumentException('Model must be an object with properties.');
        }
        $modelClass = new ModelClass($model);

        foreach($modelClass->getProperties() as $property) {

            if($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_ATTRIBUTE)) {
                $mappedValue = null;
                $attributesKey = self::ATTR_KEY;
                if(isset($source->$attributesKey) && isset($source->$attributesKey[$property->getName()])) {
                    $mappedValue = $source->$attributesKey[$property->getName()];
                }
                $property->setPropertyValue($mappedValue);
                continue;
            }

            if($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_NODE_VALUE)) {
                $mappedValue = null;
                $valueKey = self::VALUE_KEY;
                if(isset($source->$valueKey)) {
                    $mappedValue = $source->$valueKey;
                }
                $property->setPropertyValue($mappedValue);
                continue;
            }

            $sourceValue = Iteration::findValueByName($property->getName(), $source, $property->getPropertyValue());
            $mappedValue = $this->mapPropertyByType($property->getType(), $sourceValue);
            $property->setPropertyValue($mappedValue);
        }

        return $model;
    }

    /**
     * @param \DOMNode $domElement
     * @return \stdClass
     */
    protected function domNodeToObject(\DOMNode $domElement) {
        $object = new \stdClass();
        $result = null;

        $this->mapAttributes($domElement, $object);
        $this->mapNamespaces($domElement, $object);

        for($i = 0; $i < $domElement->childNodes->length; $i++) {
            $element = $domElement->childNodes->item($i);
            $isElementArray = Xml::isDomNodeArray($element->parentNode, $element->nodeName);
            switch($element->nodeType) {
                case XML_ELEMENT_NODE:
                    $result = $this->mapDomElement($element, $object, $isElementArray);
                    break;
                case XML_TEXT_NODE:
                    $result = $this->mapDomText($element, $object, $isElementArray);
                    break;
            }
        }

        return $result;
    }

    /**
     * @param \DOMNode $domElement
     * @param $object
     * @return \stdClass
     */
    protected function mapAttributes(\DOMNode $domElement, $object) {
        $attributesKey = self::ATTR_KEY;
        for($i = 0; $i < $domElement->attributes->length; $i++) {
            $key = $domElement->attributes->item($i)->nodeName;
            $value = $domElement->attributes->item($i)->nodeValue;
            $object->$attributesKey[$key] = $value;
        }
    }

    /**
     * @param \DOMNode $domElement
     * @param $object
     * @return \stdClass
     */
    protected function mapNamespaces(\DOMNode $domElement, $object) {
        $elementNamespaces = $this->getNameSpaces($domElement);
        $parentNamespaces = $this->getNameSpaces($domElement->parentNode);
        $newNamespaces = array_diff($elementNamespaces, $parentNamespaces);
        unset($newNamespaces['xmlns:xml']);

        $attributesKey = self::ATTR_KEY;
        foreach($newNamespaces as $key => $value) {
            $object->$attributesKey[$key] = $value;
        }
    }

    /**
     * @param \DOMNode $domElement
     * @return array
     */
    protected function getNameSpaces(\DOMNode $domElement) {
        $namespaces = [];
        if(!is_null($domElement->ownerDocument)) {
            $xpath = new \DOMXPath($domElement->ownerDocument);
            /** @var \DOMNode $node */
            foreach ($xpath->query('namespace::*', $domElement) as $node) {
                $namespaces[$node->nodeName] = $node->nodeValue;
            }
        }

        return $namespaces;
    }

    /**
     * @param \DOMNode $element
     * @param object $object
     * @param bool $isElementArray
     * @return mixed
     */
    protected function mapDomElement(\DOMNode $element, $object, bool $isElementArray) {
        $value = $this->domNodeToObject($element);
        $key = $element->nodeName;
        if($isElementArray) {
            Iteration::pushArrayValue($object, $key, $value);
        }
        else {
            $object->$key = $value;
        }
        $result = $object;

        return $result;
    }

    /**
     * @param \DOMNode $element
     * @param object $object
     * @param bool $isElementArray
     * @return mixed
     */
    protected function mapDomText(\DOMNode $element, $object, bool $isElementArray) {
        $value = Iteration::typeFilter($element->nodeValue);
        $result = $value;

        $attributesKey = self::ATTR_KEY;
        $valueKey = self::VALUE_KEY;
        if(isset($object->$attributesKey)) {
            $result = clone $object;
            $result->$valueKey = $value;
        }

        if($isElementArray) {
            $result = Iteration::pushArrayValue($object, $valueKey, $result);
        }

        return $result;
    }

    /**
     * @override Unmaps to an object, then converts it to xml
     * @param object $model
     * @return string
     */
    public function unmap($model) {
        $source = $this->unmapModel($model);

        $modelClass = new ModelClass($model);
        $rootName = $modelClass->getRootName();
        $xml = $this->objectToXml($source, $rootName);

        return $xml;
    }

    /**
     * @override Unmaps xml attributes and node values accordingly
     * @param object $model
     * @return \stdClass
     */
    protected function unmapModel($model) {
        if(!is_object($model) || Validation::isEmpty($model)) {
            throw new \InvalidArgumentException('Model must be an object with properties.');
        }
        $modelClass = new ModelClass($model);
        $unmappedObject = new \stdClass();
        foreach($modelClass->getProperties() as $property) {
            $propertyKey = $property->getName();
            $propertyValue = $property->getPropertyValue();
            if(Validation::isEmpty($propertyValue)) {
                continue;
            }

            if($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_ATTRIBUTE)) {
                $attributeKey = self::ATTR_KEY;
                $unmappedObject->$attributeKey[$propertyKey] = $propertyValue;
                continue;
            }

            if($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_NODE_VALUE)) {
                $valueKey = self::VALUE_KEY;
                $unmappedObject->$valueKey = $propertyValue;
                continue;
            }

            $unmappedObject->$propertyKey = $this->unmapValueByType($property->getType(), $propertyValue);
        }

        return $unmappedObject;
    }

    /**
     * @param object $source
     * @param string $elementName
     * @return string
     * @throws ModelMapperException
     */
    protected function objectToXml($source, string $elementName) {
        $elementXml = '<'.$elementName.'></'.$elementName.'>';
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($elementXml);
        $domElement = $domDocument->documentElement;

        $this->addDomElementAttributes($source, $domElement);

        $valueKey = self::VALUE_KEY;
        if(isset($source->$valueKey)) {
            if(is_bool($source->$valueKey)) {
                $source->$valueKey = ($source->$valueKey) ? 'true' : 'false';

            }
            $domElement->nodeValue = $source->$valueKey;
        }
        else {
            foreach ($source as $key => $value) {
                $this->populateDomElementByType($domElement, $key, $value);
            }
        }

        $xml = $domElement->ownerDocument->saveXML();
        $xml = str_replace("\n", "", $xml);

        return $xml;
    }

    /**
     * @param \DOMElement $domElement
     * @param string $key
     * @param mixed $value
     */
    protected function populateDomElementByType(\DOMElement $domElement, string $key, $value) {
        if(is_object($value)) {
            $this->addDomElementObject($domElement, $key, $value);
        }
        elseif(is_array($value)) {
            $this->addDomElementArray($domElement, $key, $value);
        }
        else {
            $this->addDomElement($domElement, $key, $value);
        }
    }

    /**
     * @param object $source
     * @param \DOMElement $domElement
     */
    protected function addDomElementAttributes($source, \DOMElement $domElement) {
        $attributesKey = self::ATTR_KEY;
        if(isset($source->$attributesKey) && !Validation::isEmpty($source->$attributesKey)){
            foreach($source->$attributesKey as $attrKey => $attrValue) {
                $domElement->setAttribute($attrKey, $attrValue);
            }
            unset($source->$attributesKey);
        }
    }

    /**
     * @param \DOMElement $domElement
     * @param string $key
     * @param object $value
     */
    protected function addDomElementObject(\DOMElement $domElement, string $key, $value) {
        $child = $this->createDomNode($domElement->ownerDocument, $key, $value);
        $domElement->appendChild($child);
    }

    /**
     * @param \DOMElement $domElement
     * @param $key
     * @param array $value
     */
    protected function addDomElementArray(\DOMElement $domElement, string $key, array $value) {
        foreach($value as $arrayKey => $arrayValue) {
            $this->populateDomElementByType($domElement, $key, $arrayValue);
        }
    }

    /**
     * @param \DOMElement $domElement
     * @param string $key
     * @param mixed $value
     */
    protected function addDomElement(\DOMElement $domElement, string $key, $value) {
        $child = $this->createDomElement($key, $value);
        $domElement->appendChild($child);
    }

    /**
     * @param $name
     * @param $value
     * @param null $uri
     * @return \DOMElement
     * @throws ModelMapperException
     */
    protected function createDomElement($name, $value, $uri = null) {
        if(!Xml::isValidElementName($name)) {
            throw new ModelMapperException('Property name "' . $name . '" contains invalid xml element characters.');
        }
        if(is_bool($value)) {
            $value = ($value) ? 'true' : 'false';
        }
        $element = new \DOMElement($name, $value, $uri);

        return $element;
    }

    /**
     * @param \DOMDocument $domDocument
     * @param string $name
     * @param object $value
     * @return \DOMNode
     */
    protected function createDomNode(\DOMDocument $domDocument, string $name, $value) {
        $xmlValue = $this->objectToXml($value, $name);
        $domDoc = new \DOMDocument();
        $domDoc->loadXML($xmlValue);
        $node = $domDocument->importNode($domDoc->documentElement, true);

        return $node;
    }

    /**
     * @override Added the filter for different value types, since they all come out as strings
     * @override Single xml array values would show up as non array, so logic is slightly different here
     * @param ModelPropertyType $propertyType
     * @param mixed $value
     * @return mixed
     */
    protected function mapPropertyByType(ModelPropertyType $propertyType, $value) {
        $value = Iteration::typeFilter($value);
        if($propertyType->getActualType() === TypeEnum::ARRAY && !is_array($value) ) {
            $value = [$value];
        }

        return parent::mapPropertyByType($propertyType, $value);
    }
}
