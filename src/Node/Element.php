<?php

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 11/03/17
 * Time: 11:46
 */

namespace Node;

class Element extends Node {

    /**
     * @var Node[]
     */
    protected $children = array();

    /**
     * @param string $name
     * @return boolean
     */
    public function hasChild($name) {
        return $this->hasNode($this->children, $name);
    }

    /**
     * @param $name
     * @return Node
     * @throws \Exception
     */
    public function getChild($name) {
        $node = $this->getNode($this->children, $name);
        if($node == null) {
            throw new \Exception('No child named ' . $name . ' found');
        }
        return $node;
    }

    /**
     * @param Node $newChild
     * @throws \Exception
     */
    public function addChild($newChild) {
        $childName = $newChild->getName();
        if($this->hasChild($childName)) {
            throw new \Exception('Child with name "' . $childName . '" already exists."');
        }
        $this->children[] = $newChild;
    }

    /**
     * @param string $name
     */
    public function removeChild($name) {
        $childCount = count($this->children);
        for($i=0; $i<$childCount; $i++) {
            if($this->children[$i]->getName() == $name) {
                array_splice($this->children, $i);
                break;
            }
        }
    }

    /**
     * @return Node[]
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        $value = new \stdClass();
        foreach ($this->children as $child) {
            $value->{$child->getName()} = $child->getValue();
        }
        if(empty($this->children)) {
            $value = parent::getValue();
        }
        return $value;
    }
}
