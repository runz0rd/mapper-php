<?php

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 11/03/17
 * Time: 11:46
 */

namespace Node;
use Common\Util\Iteration;

class Node {

    /**
     * @var array
     */
    protected $attributes = array();

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * @param string $name
     * @param string $value
     */
    public function __construct($name, $value = null) {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return Iteration::typeFilter($this->value);
    }

    /**
     * @param string $value
     */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasAttribute($name) {
        return isset($this->attributes[$name]);
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function getAttribute($name) {
        if(!$this->hasAttribute($name)) {
            throw new \Exception('No attribute named ' . $name . ' found');
        }
        return $this->attributes[$name];
    }

    /**
     * @param string $name
     * @param string $value
     * @throws \Exception
     */
    public function addAttribute($name, $value) {
        if($this->hasAttribute($name)) {
            throw new \Exception('Attribute named ' . $name . 'already exists');
        }
        $this->attributes[$name] = $value;
    }

    /**
     * @return Node[]
     */
    public function getAttributes() {
        return $this->attributes;
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
}

