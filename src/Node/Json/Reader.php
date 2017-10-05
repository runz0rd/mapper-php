<?php

namespace Node\Json;
use Node\ElementNode;
use Node\IReader;
use Node\TextNode;
use Node\NodeList;

class Reader implements IReader {

    /**
     * @param string $data
     * @return ElementNode
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
     * @return ElementNode
     */
    protected function parseJson($data, $name) {
        if(is_object($data)) {
            $node = new ElementNode($name);
            foreach ($data as $key => $value) {
                $node->addChild($this->parseJson($value, $key));
            }
        }
        elseif(is_array($data)) {
            $nodes = [];
            foreach ($data as $key => $value) {
                $nodes[] = $this->parseJson($value, $name);
            }
            $node = new NodeList($name, $nodes);
        }
        else {
            $node = new TextNode($name, $data);
        }
        return $node;
    }
}
