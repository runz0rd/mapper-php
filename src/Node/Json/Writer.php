<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 11/03/17
 * Time: 21:57
 */

namespace Node\Json;
use Node\IWriter;
use Node\Node;

class Writer implements IWriter {

    /**
     * @param Node $node
     * @return string
     * @throws \Exception
     */
    public function write($node) {
        $json = json_encode($node->toObject());
        if($json === false) {
            throw new \Exception('Cannot create json for given input');
        }
        return $json;
    }
}