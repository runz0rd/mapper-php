<?php

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 29/05/17
 * Time: 21:51
 */
namespace Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Destination
{
    /**
     * @var string
     */
    public $destination;

    public function __construct($values) {
        if(empty($values) || !isset($values['value'])) {
            throw new \Exception('You must specify a destination class.');
        }
        //TODO parse use statement
        if(!class_exists($values['value'])) {
            throw new \Exception('Cannot find class "' . $values['value'] . '".');
        }
        $this->destination = $values['value'];
    }
}