<?php

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 11/03/17
 * Time: 11:46
 */

namespace Node;

class ElementNode extends TextNode {

    /**
     * @var Node[]
     */
    protected $children = array();

    /**
     * @param $name
     * @return Node
     * @throws \Exception
     */
    public function getChild($name) {
        $node = null;
        foreach($this->children as $child) {
            if($child->name == $name) {
                $node = $child;
                break;
            }
        }
        return $node;
    }

    /**
     * @param Node $newChild
     * @throws \Exception
     */
    public function addChild($newChild) {
        $childName = $newChild->getName();
        if($this->getChild($childName) != null) {
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
