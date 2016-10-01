<?php

/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 6/1/2016
 * Time: 11:23 PM
 */
use Mapper\ModelMapper;

class ModelMapperTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var ModelMapper
	 */
	public $modelMapper;

	public function setUp() {
		$this->modelMapper = new ModelMapper();
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
        return [
            [null, new TestModel()],
            ['', new TestModel()],
            [1, new TestModel()],
            [false, new TestModel()],
            [array(), new TestModel()],
            [new stdClass(), new TestModel()],
            [new TestModel(), 1],
            [new TestModel(), new DateTime()],
            [new TestModel(), new stdClass()]
        ];
    }

    public function validMapValues() {
        $nestedJson1 = '{"noType":null,"boolTrue":true,"boolFalse":false,"string":"a","namedString":"named","integer":5,"array":[1,"a",3],"stringArray":["a","b","c"],"integerArray":[1,2,3],"booleanArray":[true,true,false],"objectArray":[{"a":1},{"a":1},{"a":1}],"object":{"a":1},"requiredString":null,"alwaysRequiredBoolean":true,"multipleRequiredInteger":null,"attribute1":null,"emailRule":null,"multipleRules":null}';
        $nestedJson2 = '{"noType":null,"boolTrue":true,"boolFalse":false,"string":"ab","namedString":"named","integer":5,"array":[1,"a",3],"stringArray":["a","b","c"],"integerArray":[1,2,3],"booleanArray":[true,true,false],"objectArray":[{"a":1},{"a":1},{"a":1}],"object":{"a":1},"requiredString":null,"alwaysRequiredBoolean":true,"multipleRequiredInteger":null,"attribute1":null,"emailRule":null,"multipleRules":null}';
        $json = '{"noType":null,"boolTrue":true,"boolFalse":false,"string":"a","namedString123":"named","integer":5,"array":[1,"a",3],"stringArray":["a","b","c"],"integerArray":[1,2,3],"booleanArray":[true,true,false],"objectArray":[{"a":1},{"a":1},{"a":1}],"object":{"a":1},"model":'.$nestedJson1.',"modelArray":['.$nestedJson1.', '.$nestedJson2.'],"requiredString":null,"alwaysRequiredBoolean":true,"multipleRequiredInteger":null,"attribute1":null,"emailRule":null,"multipleRules":null}';
        $source = json_decode($json);

        $object = new stdClass();
        $object->a = 1;

        $model = new TestModel();
        $model->noType = null;
        $model->boolTrue = true;
        $model->boolFalse = false;
        $model->string = 'a';
        $model->namedString = 'named';
        $model->integer = 5;
        $model->array = [1,'a',3];
        $model->stringArray = ['a','b','c'];
        $model->integerArray = [1,2,3];
        $model->booleanArray = [true,true,false];
        $model->objectArray = [$object,$object,$object];
        $model->object = $object;
        $model->alwaysRequiredBoolean = true;
        $nestedModel1 = new NestedTestModel();
        $nestedModel1->mapFromObject($model);
        $model->model = $nestedModel1;
        $nestedModel2= new NestedTestModel();
        $nestedModel2->mapFromObject($model);
        $nestedModel2->string = 'ab';
        $model->modelArray = [$nestedModel1,$nestedModel2];

        return [
            [$source, $model]
        ];
    }

    public function invalidUnmapValues() {
        return [
            [null],
            [''],
            [1,],
            [false],
            [array()],
            [new stdClass()],
            [new DateTime()]
        ];
    }

    public function validUnmapValues() {
        $nestedJson = '{"boolTrue":true,"boolFalse":false,"string":"a","namedString":"named","integer":5,"array":[1,"a",3],"stringArray":["a","b","c"],"integerArray":[1,2,3],"booleanArray":[true,true,false],"objectArray":[{"a":1},{"a":1},{"a":1}],"object":{"a":1},"alwaysRequiredBoolean":true}';
        $json = '{"boolTrue":true,"boolFalse":false,"string":"a","namedString123":"named","integer":5,"array":[1,"a",3],"stringArray":["a","b","c"],"integerArray":[1,2,3],"booleanArray":[true,true,false],"objectArray":[{"a":1},{"a":1},{"a":1}],"object":{"a":1},"model":'.$nestedJson.',"modelArray":['.$nestedJson.', '.$nestedJson.'],"alwaysRequiredBoolean":true}';
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
        $model->array = [1,'a',3];
        $model->stringArray = ['a','b','c'];
        $model->integerArray = [1,2,3];
        $model->booleanArray = [true,true,false];
        $model->objectArray = [$object,$object,$object];
        $model->object = $object;
        $model->alwaysRequiredBoolean = true;
        $nestedModel = new NestedTestModel();
        $nestedModel->mapFromObject($model);
        $model->model = $nestedModel;
        $model->modelArray = [$nestedModel,$nestedModel];

        return [
            [$model, $unmappedObject]
        ];
    }
}