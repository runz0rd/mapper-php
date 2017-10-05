<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 12/03/17
 * Time: 14:09
 */

namespace Node;


interface IReader {

    /**
     * @param mixed $data
     * @return TextNode
     */
    function read($data);

}