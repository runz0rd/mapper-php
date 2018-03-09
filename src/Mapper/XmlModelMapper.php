<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 8/9/2016
 * Time: 7:56 PM
 */

namespace Mapper;

use Annotation\XmlAnnotationEnum;
use Common\ModelReflection\Enum\AnnotationEnum;
use Common\ModelReflection\Enum\TypeEnum;
use Common\ModelReflection\ModelClass;
use Common\ModelReflection\ModelPropertyType;
use Common\Util\Iteration;
use Common\Util\Validation;
use Common\Util\Xml;


class XmlModelMapper extends ModelMapper implements IModelMapper
{

    const ATTR_KEY = '@attributes';
    const VALUE_KEY = '@value';

    /**
     * @override Converts xml to an object and then maps
     * @param string $source
     * @param object $model
     * @throws ModelMapperException
     * @return object
     */
    public function map($source, $model)
    {
        $xml = Xml::removeWhitespace($source);
        $domDocument = new \DOMDocument();
        $xmlLoadSuccess = $domDocument->loadXML($xml);
        if (!$xmlLoadSuccess) {
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
    protected function mapModel($source, $model)
    {
        if (!is_object($source) || Validation::isEmpty($source)) {
            throw new \InvalidArgumentException('Source must be an object with properties.');
        }
        if (!is_object($model) || Validation::isEmpty($model)) {
            throw new \InvalidArgumentException('Model must be an object with properties.');
        }
        $modelClass = new ModelClass($model);

        foreach ($modelClass->getProperties() as $property) {

            if ($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_ATTRIBUTE)) {
                $mappedValue = null;
                $attributesKey = self::ATTR_KEY;
                if (isset($source->$attributesKey) && isset($source->$attributesKey[$property->getName()])) {
                    $mappedValue = $source->$attributesKey[$property->getName()];
                }
                $property->setPropertyValue($mappedValue);
                continue;
            }

            if ($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_NODE_VALUE)) {
                $mappedValue = null;
                $valueKey = self::VALUE_KEY;
                if (isset($source->$valueKey)) {
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
    protected function domNodeToObject(\DOMNode $domElement)
    {
        $object = new \stdClass();
        $result = null;

        $result = $this->mapAttributes($domElement, $object);
        $result = $this->mapNamespaces($domElement, $object);

        for ($i = 0; $i < $domElement->childNodes->length; $i++) {
            $element = $domElement->childNodes->item($i);
            $isElementArray = Xml::isDomNodeArray($element->parentNode, $element->nodeName);
            switch ($element->nodeType) {
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
    protected function mapAttributes(\DOMNode $domElement, $object)
    {
        $attributesKey = self::ATTR_KEY;
        for ($i = 0; $i < $domElement->attributes->length; $i++) {
            $key = $domElement->attributes->item($i)->nodeName;
            $value = $domElement->attributes->item($i)->nodeValue;
            $object->$attributesKey[$key] = $value;
        }

        return $object;
    }

    /**
     * @param \DOMNode $domElement
     * @param $object
     * @return \stdClass
     */
    protected function mapNamespaces(\DOMNode $domElement, $object)
    {
        $elementNamespaces = $this->getNameSpaces($domElement);
        $parentNamespaces = $this->getNameSpaces($domElement->parentNode);
        $newNamespaces = array_diff($elementNamespaces, $parentNamespaces);
        unset($newNamespaces['xmlns:xml']);

        $attributesKey = self::ATTR_KEY;
        foreach ($newNamespaces as $key => $value) {
            $object->$attributesKey[$key] = $value;
        }

        return $object;
    }

    /**
     * @param \DOMNode $domElement
     * @return array
     */
    protected function getNameSpaces(\DOMNode $domElement)
    {
        $namespaces = array();
        if (!is_null($domElement->ownerDocument)) {
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
    protected function mapDomElement(\DOMNode $element, $object, $isElementArray)
    {
        $value = $this->domNodeToObject($element);
        $key = $element->nodeName;
        if ($isElementArray) {
            Iteration::pushArrayValue($object, $key, $value);
        } else {
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
    protected function mapDomText(\DOMNode $element, $object, $isElementArray)
    {
        $value = Iteration::typeFilter($element->nodeValue);
        $result = $value;

        $attributesKey = self::ATTR_KEY;
        $valueKey = self::VALUE_KEY;
        if (isset($object->$attributesKey)) {
            $result = clone $object;
            $result->$valueKey = $value;
        }

        if ($isElementArray) {
            $result = Iteration::pushArrayValue($object, $valueKey, $result);
        }

        return $result;
    }

    /**
     * @override Unmaps to an object, then converts it to xml
     * @param object $model
     * @return string
     */
    public function unmap($model)
    {
        $modelClass = new ModelClass($model);

        $source = $this->unmapModel($model);

        $rootName = $modelClass->getRootName();

        $document = $this->getDocument(
            $rootName,
            $this->getAnnotation($modelClass, XmlAnnotationEnum::XML_VERSION, '1.0'),
            $this->getAnnotation($modelClass, XmlAnnotationEnum::XML_ENCODING, 'utf-8'),
            $this->getAnnotation($modelClass, XmlAnnotationEnum::XML_NAMESPACES, null,
                function ($value) {
                    if (preg_match_all('/(([a-z]+:|)([a-zA-z]+)\=|)\"(.*?)\"/', $value, $m)) {
                        return array_combine(
                            array_map(function ($name, $prefix) {
                                return (!empty($prefix) ? rtrim($prefix, ':') : 'xmlns')
                                    . (!empty($name) ? ':' . rtrim($name, ':') : '');
                            }, $m[3], $m[2])
                            , $m[4]);
                    }
                }

            )
        );

        return $this->objectToXml($source, $document);
    }

    private function getAnnotation(
        ModelClass $classInfo,
        $xmlAnnotationName,
        $default = null,
        $annotationHandler = null
    ) {
        $value = $default;
        if ($classInfo->getDocBlock()->hasAnnotation($xmlAnnotationName) &&
            !Validation::isEmpty($classInfo->getDocBlock()->getAnnotation($xmlAnnotationName))) {
            $value = $classInfo->getDocBlock()->getFirstAnnotation($xmlAnnotationName);
            if (isset($annotationHandler) && is_callable($annotationHandler)) {
                $value = call_user_func_array($annotationHandler, ['value' => $value]);
            }
        }
        return $value;
    }

    /**
     * @override Unmaps xml attributes and node values accordingly
     * @param object $model
     * @return \stdClass
     */
    protected function unmapModel($model)
    {
        if (!is_object($model) || Validation::isEmpty($model)) {
            throw new \InvalidArgumentException('Model must be an object with properties.');
        }
        $modelClass = new ModelClass($model);
        $unmappedObject = new \stdClass();

        foreach ($modelClass->getProperties() as $property) {
            $propertyKey = $property->getName();
            $propertyValue = $property->getPropertyValue();
            if (Validation::isEmpty($propertyValue)) {
                continue;
            }
            if ($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_ATTRIBUTE)) {
                $attributeKey = self::ATTR_KEY;
                $unmappedObject->$attributeKey[$propertyKey] = $propertyValue;
                continue;
            }

            if ($property->getDocBlock()->hasAnnotation(AnnotationEnum::XML_NODE_VALUE)) {
                $valueKey = self::VALUE_KEY;
                $unmappedObject->$valueKey = $propertyValue;
                continue;
            }

            $unmappedObject->$propertyKey = $this->unmapValueByType($property->getType(), $propertyValue);
        }

        return $unmappedObject;
    }

    /**
     * @param string $root
     * @param string $version
     * @param string $encoding
     * @param null $namespaces
     * @return \DOMDocument
     */
    protected function getDocument($root, $version = "1.0", $encoding = "UTF-8", $namespaces = null)
    {
        $domDocument = new \DOMDocument();
        $domDocument->version = $version;
        $domDocument->encoding = $encoding;

        $domElement = $domDocument->createElement($root);

        if (isset($namespaces)) {
            foreach ($namespaces as $qualifiedName => $namespaceURI) {
                try {
                    $domElement->setAttributeNS('http://www.w3.org/2000/xmlns/',
                        $qualifiedName, $namespaceURI);
                } catch (\DOMException $e) {
                    $domElement->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance',
                        $qualifiedName, $namespaceURI);
                }
            }
        }
        $domDocument->appendChild($domElement);

        return $domDocument;
    }

    /**
     * @param $source
     * @param \DOMElement $domElement
     * @internal param \DOMElement $element
     */
    protected function processTheSources($source, \DOMElement $domElement)
    {

        $this->addDomElementAttributes($source, $domElement);

        $valueKey = self::VALUE_KEY;
        if (isset($source->$valueKey)) {
            if (is_bool($source->$valueKey)) {
                $source->$valueKey = ($source->$valueKey) ? 'true' : 'false';

            }
            $domElement->nodeValue = $source->$valueKey;
        } else {
            foreach ($source as $key => $value) {
                $this->populateDomElementByType($domElement, $key, $value);
            }
        }
    }

    /**
     * @param object $source
     * @param $domDocument
     * @return string
     */
    protected function objectToXml($source, $domDocument)
    {

        $domElement = $domDocument->documentElement;

        $this->processTheSources($source, $domElement);

        $xml = $domElement->ownerDocument->saveXML();
        $xml = str_replace("\n", "", $xml);

        return $xml;
    }

    /**
     * @param \DOMElement $domElement
     * @param string $key
     * @param mixed $value
     */
    protected function populateDomElementByType(\DOMElement $domElement, $key, $value)
    {
        if (is_object($value)) {
            $this->addDomElementObject($domElement, $key, $value);
        } elseif (is_array($value)) {
            $this->addDomElementArray($domElement, $key, $value);
        } else {
            $this->addDomElement($domElement, $key, $value);
        }
    }

    /**
     * @param object $source
     * @param \DOMElement $domElement
     */
    protected function addDomElementAttributes($source, \DOMElement $domElement)
    {
        $attributesKey = self::ATTR_KEY;
        if (isset($source->$attributesKey) && !Validation::isEmpty($source->$attributesKey)) {
            foreach ($source->$attributesKey as $attrKey => $attrValue) {
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
    protected function addDomElementObject(\DOMElement $domElement, $key, $value)
    {
        $child = $this->createDomNode($domElement->ownerDocument, $key, $value);
        $domElement->appendChild($child);
    }

    /**
     * @param \DOMElement $domElement
     * @param $key
     * @param array $value
     */
    protected function addDomElementArray(\DOMElement $domElement, $key, array $value)
    {
        foreach ($value as $arrayKey => $arrayValue) {
            $this->populateDomElementByType($domElement, $key, $arrayValue);
        }
    }

    /**
     * @param \DOMElement $domElement
     * @param string $key
     * @param mixed $value
     */
    protected function addDomElement(\DOMElement $domElement, $key, $value)
    {
        $child = $this->createDomElement($key, $value, $domElement->ownerDocument);
        $domElement->appendChild($child);
    }

    /**
     * @param $name
     * @param $value
     * @param \DOMDocument $document
     * @return \DOMElement
     * @throws ModelMapperException
     * @internal param null $uri
     */
    protected function createDomElement($name, $value, \DOMDocument $document)
    {

        if (is_bool($value)) {
            $value = ($value) ? 'true' : 'false';
        }

        try {
            $element = $document->createElement($name, $value);
        } catch (\DOMException $e) {
            throw new ModelMapperException('Property name "' . $name . '" contains invalid xml element characters.'
                . $e->getMessage(), 0, $e);
        }

        return $element;
    }

    /**
     * @param \DOMDocument $domDocument
     * @param string $name
     * @param object $value
     * @return \DOMNode
     */
    protected function createDomNode(\DOMDocument $domDocument, $name, $value)
    {
        $node = $domDocument->createElement($name);
        $this->processTheSources($value, $node);
        return $node;
    }

    /**
     * @override Added the filter for different value types, since they all come out as strings
     * @override Single xml array values would show up as non array, so logic is slightly different here
     * @param ModelPropertyType $propertyType
     * @param mixed $value
     * @return mixed
     */
    protected function mapPropertyByType(ModelPropertyType $propertyType, $value)
    {
        $value = Iteration::typeFilter($value);
        if ($propertyType->getActualType() === TypeEnum::ARR && !is_array($value) && !is_null($value)) {
            $value = array($value);
        }

        return parent::mapPropertyByType($propertyType, $value);
    }
}
