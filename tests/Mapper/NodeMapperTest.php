<?php

/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 6/1/2016
 * Time: 11:23 PM
 */
use Mapper\ModelMapper;

class NodeMapperTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \Mapper\NodeMapper
	 */
	public $modelMapper;

	public function setUp() {
		$this->modelMapper = new \Mapper\NodeMapper();
		parent::setUp();
	}

    /**
     * @param $source
     * @param $expectedModel
     * @dataProvider validMapValues
     */
	public function testMap($source, $expectedModel) {
        $actualModel = $this->modelMapper->map($source, new TestModel());
        $this->assertEquals($expectedModel, $actualModel);
	}

    /**
     * @param $source
     * @param $model
     * @dataProvider invalidMapValues
     * @expectedException \Exception
     */
    public function testMapFail($source, $model) {
        $this->modelMapper->map($source, $model);
    }

    /**
     * @param $expectedObject
     * @param $model
     * @dataProvider validUnmapValues
     */
    public function testUnmap($model, $expectedObject) {
        $actualObject = $this->modelMapper->unmap($model);
        $this->assertEquals($expectedObject, $actualObject);
    }

    /**
     * @param $model
     * @dataProvider invalidUnmapValues
     * @expectedException \Exception
     */
    public function testUnmapFail($model) {
        $this->modelMapper->unmap($model);
    }

    public function invalidMapValues() {
        return array(
            array(null, new TestModel()),
            array('', new TestModel()),
            array(1, new TestModel()),
            array(false, new TestModel()),
            array(array(), new TestModel()),
            array(new stdClass(), new TestModel()),
            array(new TestModel(), 1),
            array(new TestModel(), new DateTime()),
            array(new TestModel(), new stdClass())
        );
    }

    public function validMapValues() {
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
        $model->xml = new XmlTestModel();
        $model->xml->ns = 'testns1';
        $model->xml->attributeTest = 'attribute';
        $model->xml->value = 'nodeValue';
        $model->xmlWithoutValue = new XmlTestModel();
        $model->xmlWithoutValue->attributeTest = 'attribute';
        $model->xmlWithoutValue->ns = 'testns2';

        $xml = \Common\Util\Xml::loadFromFile(__DIR__ . '/../testFiles/valid.xml');
        $reader = new \Node\Xml\Reader();
        $source = $reader->read($xml);

        return array(
            array($source, $model)
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
        $nestedJson = '{"boolTrue":true,"boolFalse":false,"string":"a","namedString":"named","integer":5,"array":[1,"a",3],"stringArray":["a","b","c"],"integerArray":[3],"booleanArray":[true,true,false],"objectArray":[{"a":1},{"a":1},{"a":1}],"object":{"a":1},"alwaysRequiredBoolean":true}';
        $json = '{"boolTrue":true,"boolFalse":false,"string":"a","namedString123":"named","integer":5,"array":[1,"a",3],"stringArray":["a","b","c"],"integerArray":[3],"booleanArray":[true,true,false],"objectArray":[{"a":1},{"a":1},{"a":1}],"object":{"a":1},"model":'.$nestedJson.',"modelArray":['.$nestedJson.', '.$nestedJson.'],"alwaysRequiredBoolean":true}';
        $unmappedObject = json_decode($json);

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