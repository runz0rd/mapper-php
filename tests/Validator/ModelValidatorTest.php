<?php

/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 8/6/2016
 * Time: 7:42 PM
 */
use Validator\ModelValidator;

class ModelValidatorTest extends PHPUnit_Framework_TestCase {

    /**
     * @var ModelValidator
     */
    public $modelValidator;

    public function setUp() {
        $this->modelValidator = new ModelValidator();
        parent::setUp();
    }

    /**
     * @param $validModel
     * @param $requiredType
     * @dataProvider validModels
     */
    public function testValidate($validModel, $requiredType) {
        $this->modelValidator->validate($validModel, $requiredType);
    }

    /**
     * @param $invalidModel
     * @param $requiredType
     * @dataProvider invalidModels
     * @expectedException Exception
     */
    public function testValidateFail($invalidModel, $requiredType) {
        $this->modelValidator->validate($invalidModel, $requiredType);
    }

    public function validModels() {
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
        $model->requiredString = 'requiredString';
        $model->alwaysRequiredBoolean = false;
        $model->multipleRequiredInteger = 5;
        $nestedModel = new NestedTestModel();
        $nestedModel->mapFromObject($model);
        $model->model = $nestedModel;
        $model->modelArray = [$nestedModel,$nestedModel];
        $model->emailRule = 'test@test.com';
        $model->multipleRules = 'test@test.com';

        return [
            [$model, 'requiredString'],
            [$model, ''],
            [$model, 'requiredInteger'],
            [$model, 'testRequired']
        ];
    }

    public function invalidModels() {
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
        $model->requiredString = 'requiredString';
        $model->alwaysRequiredBoolean = false;
        $model->multipleRequiredInteger = 5;
        $nestedModel = new NestedTestModel();
        $nestedModel->mapFromObject($model);
        $model->model = $nestedModel;
        $model->modelArray = [$nestedModel,$nestedModel];
        $model->emailRule = 'test@test.com';
        $model->multipleRules = 'test@test.com';

        $invalidModel1 = clone $model;
        $invalidModel1->boolTrue = '234';

        $invalidModel2 = clone $model;
        $invalidModel2->string = 5;

        $invalidModel3 = clone $model;
        $invalidModel3->integer = '3as';

        $invalidModel4 = clone $model;
        $invalidModel4->array = true;

        $invalidModel5 = clone $model;
        $invalidModel5->object = 'asd';

        $invalidModel6 = clone $model;
        $invalidModel6->model = 67;

        $invalidModel7 = clone $model;
        $invalidModel7->modelArray = false;

        $invalidModel8 = clone $model;
        $invalidModel8->requiredString = '';

        $invalidModel9 = clone $model;
        $invalidModel9->requiredString = null;

        $invalidModel10 = clone $model;
        $invalidModel10->requiredString = array();

        $invalidModel11 = clone $model;
        $invalidModel11->alwaysRequiredBoolean = '';

        $invalidModel12 = clone $model;
        $invalidModel12->alwaysRequiredBoolean = null;

        $invalidModel13 = clone $model;
        $invalidModel13->alwaysRequiredBoolean = 123;

        $invalidModel14 = clone $model;
        $invalidModel14->multipleRequiredInteger = '';

        $invalidModel15 = clone $model;
        $invalidModel15->multipleRequiredInteger = null;

        $invalidModel16 = clone $model;
        $invalidModel16->multipleRequiredInteger = 'asd';

        $invalidModel17 = clone $model;
        $invalidModel17->emailRule = 'invalidEmail';

        $invalidModel18 = clone $model;
        $invalidModel18->multipleRules = 3;

        $invalidModel19 = clone $model;
        $invalidModel19->stringArray = [3];

        $invalidModel20 = clone $model;
        $invalidModel20->integerArray = [3,'asd'];

        $invalidModel21 = clone $model;
        $invalidModel21->booleanArray = [true,'asd'];

        $invalidModel22 = clone $model;
        $invalidModel22->objectArray = [new stdClass(),'asd'];

        return [
            [$invalidModel1, ''],
            [$invalidModel2, ''],
            [$invalidModel3, ''],
            [$invalidModel4, ''],
            [$invalidModel5, ''],
            [$invalidModel6, ''],
            [$invalidModel7, ''],
            [$invalidModel8, 'requiredString'],
            [$invalidModel9, 'requiredString'],
            [$invalidModel10, 'requiredString'],
            [$invalidModel11, ''],
            [$invalidModel12, ''],
            [$invalidModel13, ''],
            [$invalidModel14, 'requiredInteger'],
            [$invalidModel15, 'requiredInteger'],
            [$invalidModel16, 'requiredInteger'],
            [$invalidModel14, 'testRequired'],
            [$invalidModel15, 'testRequired'],
            [$invalidModel16, 'testRequired'],
            [$invalidModel17, ''],
            [$invalidModel18, ''],
            [$invalidModel19, ''],
            [$invalidModel20, ''],
            [$invalidModel21, ''],
            [$invalidModel22, '']
        ];
    }
}
