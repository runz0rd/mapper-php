<?php

namespace Node\Xml;
use Node\Element;
use Node\IReader;

class Reader implements IReader {

    /**
     * @var \XMLReader
     */
    public $reader;


    /**
     * @param string $data
     * @return Element
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
     * @return Element
     */
    protected function parseNode() {
        $node = new Element($this->reader->name);
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
                $node->addChild($this->parseNode());
            }
            if($this->reader->nodeType == \XMLReader::TEXT) {
                $node->setValue($this->reader->value);
            }
        }
        return $node;
    }
}
