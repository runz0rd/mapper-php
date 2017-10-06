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
        $value = $this->filterInteger($this->value);
        $value = $this->filterBoolean($value);
        return $value;
    }

    /**
     * @param string $value
     */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * Returns a casted integer or the original value
     * @param mixed $value
     * @return integer|mixed
     */
    protected function filterInteger($value) {
        $intValue = filter_var($value, FILTER_VALIDATE_INT);
        if($intValue !== false && is_string($value)) {
            $value = $intValue;
        }

        return $value;
    }

    /**
     * Returns a casted boolean or the original value
     * @param mixed $value
     * @return boolean|mixed
     */
    protected function filterBoolean($value) {
        $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if(!is_null($boolValue) && preg_match('/(true|false)/i', $value)) {
            $value = $boolValue;
        }

        return $value;
    }
}

