<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 15/04/17
 * Time: 16:35
 */

namespace Node;


class NodeList extends Node {

    /**
     * @var Node[]
     */
    protected $nodes;

    /**
     * NodeList constructor.
     * @param string $name
     * @param Node[] $nodes
     */
    public function __construct($name, $nodes) {
        $this->nodes = $nodes;
        parent::__construct($name);
    }

    /**
     * @param Node $node
     */
    public function addNode($node) {
        $this->nodes[] = $node;
    }

    /**
     * @return Node[]
     */
    public function getNodes() {
        return $this->nodes;
    }

    /**
     * @param string $value
     * @throws \Exception
     */
    public function setValue($value) {
        throw new \Exception('NodeList cannot have a text value.');
    }

    /**
     * @return array
     */
    public function getValue() {
        $value = array();
        foreach ($this->nodes as $node) {
            $value[] = $node->getValue();
        }
        return $value;
    }
}