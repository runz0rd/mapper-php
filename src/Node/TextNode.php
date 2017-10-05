<?php

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 11/03/17
 * Time: 11:46
 */

namespace Node;

class TextNode extends Node {

    /**
     * @var Node[]
     */
    protected $attributes = array();

    /**
     * @param string $name
     * @return Node|null
     */
    public function getAttribute($name) {
        return $this->getNode($this->attributes, $name);
    }

    /**
     * @param string $name
     * @param string $value
     * @throws \Exception
     */
    public function addAttribute($name, $value) {
        if($this->getAttribute($name) != null) {
            throw new \Exception('Attribute named ' . $name . ' already set.');
        }
        $this->attributes[] = new Node($name, $value);
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

