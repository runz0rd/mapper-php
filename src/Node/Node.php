<?php

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 11/03/17
 * Time: 11:46
 */

namespace Node;

class Node {

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
     * @return string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value) {
        $this->value = $value;
    }
}

