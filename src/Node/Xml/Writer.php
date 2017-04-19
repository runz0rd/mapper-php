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
        $this->writer->startElement($node->getName());
        if($node instanceof Element) {
            foreach ($node->getAttributes() as $attribute) {
                $this->writer->writeAttribute($attribute->getName(), $attribute->getValue());
            }
            foreach ($node->getAllChildren() as $child) {
                $this->writeNode($child);
            }
        }
        if($node->getValue() != null) {
            $this->writer->text($node->getValue());
        }
        $this->writer->endElement();
    }
}