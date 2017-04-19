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
    protected $attributes = array();

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
     * @return Node[]
     * @throws \Exception
     */
    public function getChildren($name) {
        $children = array();
        foreach($this->children as $item) {
            if($item->name == $name) {
                $children[] = $item;
            }
        }
        if(empty($children)) {
            throw new \Exception('No child named ' . $name . ' found');
        }
        return $children;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasAttribute($name) {
        return $this->hasNode($this->attributes, $name);
    }

    /**
     * @param string$name
     * @return Node
     * @throws \Exception
     */
    public function getAttribute($name) {
        $node = $this->getNode($this->attributes, $name);
        if($node == null) {
            throw new \Exception('No attribute named ' . $name . ' found');
        }
        return $node;
    }

    /**
     * @param string $name
     * @param string $value
     * @throws \Exception
     */
    public function addAttribute($name, $value) {
        foreach($this->attributes as $node) {
            if($node->name == $name) {
                $attribute = $node;
                break;
            }
        }
        if(isset($attribute)) {
            throw new \Exception('Attribute named ' . $name . 'already exists');
        }
        $this->attributes[] = new Node($name, $value);
    }

    /**
     * @param Node $newChild
     */
    public function addChild($newChild) {
        $this->children[] = $newChild;
    }

    /**
     * @param string $name
     */
    public function removeChild($name) {
        for($i=0; $i<count($this->children); $i++) {
            if($this->children[$i]->name == $name) {
                unset($this->children[$i]);
                break;
            }
        }
    }

    /**
     * @return Node[]
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * @return Node[]
     */
    public function getAllChildren() {
        return $this->children;
    }

    /**
     * @param Node[] $source
     * @param string $name
     * @return boolean
     */
    protected function hasNode($source, $name) {
        $result = false;
        foreach($source as $node) {
            if($node->name == $name) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * @param Node[] $source
     * @param string $name
     * @return Node|null
     */
    protected function getNode($source, $name) {
        $node = null;
        foreach($source as $item) {
            if($item->name == $name) {
                $node = $item;
                break;
            }
        }
        return $node;
    }

    /**
     * @return \stdClass|mixed
     */
    public function toObject() {
        //TODO revise naming
        $object = new \stdClass();
        foreach ($this->children as $child) {
            $object->{$child->getName()} = $child->toObject();
        }
        if(empty($this->children)) {
            $object = $this->getValue();
        }
        return $object;
    }

}
