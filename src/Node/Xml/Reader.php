<?php

namespace Node\Xml;
use Node\ElementNode;
use Node\IReader;
use Node\TextNode;
use Node\NodeList;

class Reader implements IReader {

    /**
     * @var \XMLReader
     */
    public $reader;


    /**
     * @param string $data
     * @return ElementNode
     * @throws \Exception
     */
    public function read($data) {
        $this->reader = new \XmlReader();
        if(!$this->reader->XML($data)) {
            throw new \Exception('Invalid XML provided.');
        }
        while($this->reader->read() && $this->reader->nodeType == \XMLReader::ELEMENT) {
            $rootElement = $this->parseNode();
        }
        $this->reader->close();
        if(!isset($rootElement)) {
            throw new \Exception();
        }
        return $rootElement;
    }

    /**
     * @return ElementNode
     */
    protected function parseNode() {
        $node = new ElementNode($this->reader->name);
        if($this->reader->moveToFirstAttribute()) {
            $node->addAttribute($this->reader->name, $this->reader->value);
            while($this->reader->moveToNextAttribute()) {
                $node->addAttribute($this->reader->name, $this->reader->value);
            }
        }
        while($this->reader->read()) {
            if($this->reader->nodeType == \XMLReader::END_ELEMENT && $this->reader->name == $node->getName()) {
                break;
            }
            if($this->reader->nodeType == \XMLReader::ELEMENT) {
                $this->addChild($node, $this->parseNode());
            }
            if($this->reader->nodeType == \XMLReader::TEXT) {
                $node->setValue($this->reader->value);
            }
        }
        return $node;
    }

    /**
     * @param ElementNode $parent
     * @param TextNode $newChild
     */
    protected function addChild($parent, $newChild) {
        $childName = $newChild->getName();
        if($parent->hasChild($childName)) {
            $child = $parent->getChild($childName);
            if($child instanceof NodeList) {
                $child->addNode($newChild);
            }
            else {
                $parent->removeChild($childName);
                $parent->addChild(new NodeList($childName, [$child, $newChild]));
            }
        }
        else {
            $parent->addChild($newChild);
        }
    }
}
