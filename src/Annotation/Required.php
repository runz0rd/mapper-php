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
class Required
{
    /**
     * @var string
     */
    public $action;

    public function __construct($values) {
        $this->action = 'any';
        if(isset($values['value'])) {
            $this->action = $values['value'];
        }
    }
}