<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 11/03/17
 * Time: 21:57
 */

namespace Node\Xml;
use Node\Element;
use Node\Node;
use Node\IWriter;
use Node\NodeList;

class Writer implements IWriter {

    /**
     * @var \XMLWriter
     */
    private $writer;

    /**
     * Writer constructor.
     * @param bool $useIndent
     */
    public function __construct($useIndent = false) {
        $this->writer = new \XMLWriter();
        $this->writer->openMemory();
        $this->writer->setIndent($useIndent);
    }

    public function write($node) {
        $this->writer->startDocument();
        $this->writeNode($node);
        $this->writer->endDocument();
        return $this->writer->outputMemory();
    }

    /**
     * @param Node $node
     */
    protected function writeNode($node) {
        if($node instanceof Element) {
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
     * @param Node $node
     */
    protected function writeAttributes($node) {
        foreach ($node->getAttributes() as $attributeKey => $attributeValue) {
            $this->writer->writeAttribute($attributeKey, $attributeValue);
        }
    }

    /**
     * @param Element $node
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