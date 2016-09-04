<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 8/20/2016
 * Time: 9:42 AM
 */

namespace Validator;

use Common\ModelReflection\ModelProperty;

interface IRule {

    /**
     * Used in your @rule annotation (single value)
     * Can have aliases (hence the array)
     * Case insensitive
     * @return array
     */
    function getNames();

    /**
     * Define your rule and have your property pass it
     * Additional rule parameters are stored inside $params
     * Should throw an Exception on failure
     * @param ModelProperty $property
     * @param array $params
     * @throws \Throwable
     */
    function validate(ModelProperty $property, array $params = []);
}