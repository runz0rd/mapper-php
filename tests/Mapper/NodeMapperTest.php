<?php

/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 6/1/2016
 * Time: 11:23 PM
 */

class NodeMapperTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \Mapper\NodeMapper
	 */
	public $nodeMapper;

	public function setUp() {
		$this->nodeMapper = new \Mapper\NodeMapper();
		parent::setUp();
	}

    /**
     * @param $node
     * @param $expectedModel
     * @dataProvider validValues
     */
	public function testMap($node, $expectedModel) {
        $actualModel = $this->nodeMapper->map($node, new TestModel());
        $this->assertEquals($expectedModel, $actualModel);
	}

    /**
     * @param $node
     * @param $model
     * @dataProvider invalidMapValues
     * @expectedException \Exception
     */
    public function testMapFail($node, $model) {
        $this->nodeMapper->map($node, $model);
    }

//    /**
//     * @param $expectedObject
//     * @param $model
//     * @dataProvider validValues
//     */
//    public function testUnmap($model, $expectedNode) {
//        $actualNode = $this->nodeMapper->unmap($model);
//        $this->assertEquals($expectedNode, $actualNode);
//    }

//    /**
//     * @param $model
//     * @dataProvider invalidUnmapValues
//     * @expectedException \Exception
//     */
//    public function testUnmapFail($model) {
//        $this->nodeMapper->unmap($model);
//    }

    public function invalidMapValues() {
        return array(
            array(null, new TestModel()),
            array('', new TestModel()),
            array(1, new TestModel()),
            array(false, new TestModel()),
            array(array(), new TestModel()),
            array(new stdClass(), new TestModel()),
            array(new TestModel(), 1),
            array(new \Node\TextNode('asd'), new DateTime()),
            array(new TestModel(), new stdClass())
        );
    }

    public function validValues() {
        $object = new stdClass();
        $object->a = 1;

        $model = new TestModel();
        $model->attribute1 = 'attribute1';
        $model->noType = null;
        $model->boolTrue = true;
        $model->boolFalse = false;
        $model->string = 'a';
        $model->namedString = 'named';
        $model->integer = 5;
        $model->array = array(1,'a',3);
        $model->stringArray = array('a','b','c');
        $model->integerArray = array(3);
        $model->booleanArray = array(true,true,false);
        $model->objectArray = array($object,$object,$object);
        $model->object = $object;
        $model->requiredString = 'requiredString';
        $model->alwaysRequiredBoolean = false;
        $model->multipleRequiredInteger = 5;
        $nestedModel1 = new NestedTestModel();
        $nestedModel1->mapFromObject($model);
        $nestedModel1->attribute1 = 'attribute2';
        $nestedModel2 = clone $nestedModel1;
        $nestedModel2->mapFromObject($model);
        $nestedModel2->attribute1 = 'attribute3';
        $model->model = $nestedModel1;
        $model->modelArray = array($nestedModel1,$nestedModel2);

        $xmlModel = clone $model;
        $xmlModel->xml = new XmlTestModel();
        $xmlModel->xml->ns = 'testns1';
        $xmlModel->xml->attributeTest = 'attribute';
        $xmlModel->xml->value = 'nodeValue';
        $xmlModel->xmlWithoutValue = new XmlTestModel();
        $xmlModel->xmlWithoutValue->attributeTest = 'attribute';
        $xmlModel->xmlWithoutValue->ns = 'testns2';

        $xml = \Common\Util\Xml::loadFromFile(__DIR__ . '/../testFiles/valid.xml');
        $xmlReader = new \Node\Xml\Reader();
        $xmlNode = $xmlReader->read($xml);

        $json = \Common\Util\File::read(__DIR__ . '/../testFiles/valid.json');
        $jsonReader = new \Node\Json\Reader();
        $jsonNode = $jsonReader->read($json);

        return array(
            array($xmlNode, $xmlModel),
            array($jsonNode, $model)
        );
    }

    public function invalidUnmapValues() {
        return array(
            array(null),
            array(''),
            array(1,),
            array(false),
            array(array()),
            array(new stdClass()),
            array(new DateTime())
        );
    }

    public function validUnmapValues() {
        // $nestedJson = '{"boolTrue":true,"boolFalse":false,"string":"a","namedString":"named","integer":5,"array":[1,"a",3],"stringArray":["a","b","c"],"integerArray":[3],"booleanArray":[true,true,false],"objectArray":[{"a":1},{"a":1},{"a":1}],"object":{"a":1},"alwaysRequiredBoolean":true}';
        // $json = '{"boolTrue":true,"boolFalse":false,"string":"a","namedString123":"named","integer":5,"array":[1,"a",3],"stringArray":["a","b","c"],"integerArray":[3],"booleanArray":[true,true,false],"objectArray":[{"a":1},{"a":1},{"a":1}],"object":{"a":1},"model":'.$nestedJson.',"modelArray":['.$nestedJson.', '.$nestedJson.'],"alwaysRequiredBoolean":true}';
        $json = \Common\Util\File::read(__DIR__ . '/../testFiles/valid.json');
        $unmappedObject = json_decode($json);
        echo $json;

        $object = new stdClass();
        $object->a = 1;

        $model = new TestModel();
        $model->noType = null;
        $model->boolTrue = true;
        $model->boolFalse = false;
        $model->string = 'a';
        $model->namedString = 'named';
        $model->integer = 5;
        $model->array = array(1,'a',3);
        $model->stringArray = array('a','b','c');
        $model->integerArray = array(3);
        $model->booleanArray = array(true,true,false);
        $model->objectArray = array($object,$object,$object);
        $model->object = $object;
        $model->alwaysRequiredBoolean = true;
        $nestedModel = new NestedTestModel();
        $nestedModel->mapFromObject($model);
        $model->model = $nestedModel;
        $model->modelArray = array($nestedModel,$nestedModel);

        return array(
            array($model, $unmappedObject)
        );
    }
}