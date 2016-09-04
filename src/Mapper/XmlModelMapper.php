<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 8/9/2016
 * Time: 7:56 PM
 */

namespace Mapper;
use Common\Util\Iteration;
use Common\Util\Validation;
use Common\Util\Xml;

class XmlModelMapper extends ModelMapper implements IModelMapper {

    /**
     * @param mixed $source
     * @param object $model
     * @return object
     */
    public function map($source, $model) {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($source);
        $domElement = $domDocument->documentElement;
        $object = $this->domNodeToObject($domElement);
        $model = parent::map($object, $model);

        return $model;
    }

    /**
     * @param object $model
     * @return string
     */
    public function unmap($model) {
        $object = parent::unmap($model);
        $rootName = parent::getModelRootName($model);
        $xml = $this->objectToXml($object, $rootName);

        return $xml;
    }

    /**
     * @param \DOMNode $domElement
     * @return \stdClass
     */
    protected function domNodeToObject(\DOMNode $domElement) {
        $object = new \stdClass();
        $result = null;

        $object = $this->mapAttributes($domElement, $object);

        for($i = 0; $i < $domElement->childNodes->length; $i++) {
            $element = $domElement->childNodes->item($i);
            $result = $this->mapByDomNodeType($element, $object);
        }

        return $result;
    }

    /**
     * @param \DOMNode $domElement
     * @param $object
     * @return \stdClass
     */
    protected function mapAttributes(\DOMNode $domElement, $object) {
        $attributesKey = parent::ATTR_KEY;
        for($i = 0; $i < $domElement->attributes->length; $i++) {
            $key = $domElement->attributes->item($i)->nodeName;
            $value = $domElement->attributes->item($i)->nodeValue;
            $object->$attributesKey[$key] = $value;
        }

        return $object;
    }

    /**
     * @param \DOMNode $element
     * @param object $object
     * @return mixed
     */
    protected function mapByDomNodeType(\DOMNode $element, $object) {
        $isElementArray = Xml::isDomNodeArray($element->parentNode, $element->nodeName);
        switch($element->nodeType) {
            case XML_ELEMENT_NODE:
                $object = $this->mapDomElement($element, $object, $isElementArray);
                break;
            case XML_TEXT_NODE:
                $object = $this->mapDomText($element, $object, $isElementArray);
                break;
        }

        return $object;
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
            $result = Iteration::pushArrayValue($object, $key, $value);
        }
        else {
            $object->$key = $value;
            $result = $object;
        }

        return $result;
    }

    /**
     * @param \DOMNode $element
     * @param object $object
     * @param bool $isElementArray
     * @return mixed
     */
    protected function mapDomText(\DOMNode $element, $object, bool $isElementArray) {
        /** @var \DOMElement $element->parentNode */
        $key = $element->parentNode->tagName;
        $value = Iteration::typeFilter($element->nodeValue);
        $attributesKey = $attributesKey = parent::ATTR_KEY;

        $result = $value;
        if(isset($object->$attributesKey)) {
            $result = clone $object;
            $result->$key = $value;
        }

        if($isElementArray) {
            $result = Iteration::pushArrayValue($object, $key, $result);
        }

        return $result;
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

        foreach($source as $key => $value) {
            $this->populateDomElementByType($domElement, $key, $value);
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
        $attributesKey = parent::ATTR_KEY;
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
}
