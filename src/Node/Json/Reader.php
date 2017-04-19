<?php

namespace Node\Json;
use Node\Element;
use Node\IReader;
use Node\NodeList;

class Reader implements IReader {

    /**
     * @param string $data
     * @return Element
     * @throws \Exception
     */
    public function read($data) {
        $object= json_decode($data);
        if($object == null) {
            throw new \Exception('Cannot read json data.');
        }
        $node = $this->parseJson($object, 'root');
        return $node;
    }

    /**
     * @param mixed $data
     * @param string $name
     * @return Element
     */
    protected function parseJson($data, $name) {
        $node = new Element($name);
        if(is_object($data)) {
            foreach ($data as $key => $value) {
                $child = $this->parseJson($value, $key);
                $node->addChild($child);
            }
        }
        elseif(is_array($data)) {
            $children = array();
            foreach ($data as $key => $value) {
                $children[] = $this->parseJson($value, $key);
            }
            $node->addChild(new NodeList($name, $children));
        }
        else {
            $node->setValue($data);
        }
        return $node;
    }
}
