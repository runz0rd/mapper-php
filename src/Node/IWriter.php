<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 12/03/17
 * Time: 14:09
 */

namespace Node;


interface IWriter {

    /**
     * @param ElementNode $node
     * @return mixed
     */
    function write($node);

}