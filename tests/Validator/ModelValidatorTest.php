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
        $model->array = array(1,'a',3);
        $model->stringArray = array('a','b','c');
        $model->integerArray = array(1,2,3);
        $model->booleanArray = array(true,true,false);
        $model->objectArray = array($object,$object,$object);
        $model->object = $object;
        $model->requiredString = 'requiredString';
        $model->alwaysRequiredBoolean = false;
        $model->multipleRequiredInteger = 5;
        $nestedModel = new NestedTestModel();
        $nestedModel->mapFromObject($model);
        $model->model = $nestedModel;
        $model->modelArray = array($nestedModel,$nestedModel);
        $model->emailRule = 'test@test.com';
        $model->multipleRules = '192.168.0.1';

        return [
            array($model, 'requiredString'),
            array($model, ''),
            array($model, 'requiredInteger'),
            array($model, 'testRequired')
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
        $model->array = array(1,'a',3);
        $model->stringArray = array('a','b','c');
        $model->integerArray = array(1,2,3);
        $model->booleanArray = array(true,true,false);
        $model->objectArray = array($object,$object,$object);
        $model->object = $object;
        $model->requiredString = 'requiredString';
        $model->alwaysRequiredBoolean = false;
        $model->multipleRequiredInteger = 5;
        $nestedModel = new NestedTestModel();
        $nestedModel->mapFromObject($model);
        $model->model = $nestedModel;
        $model->modelArray = array($nestedModel,$nestedModel);
        $model->emailRule = 'test@test.com';
        $model->multipleRules = 'test@test.com';

        $invalidModel1 = clone $model;
        $invalidModel1->boolTrue = 'false';

        $invalidModel2 = clone $model;
        $invalidModel2->string = 5;

        $invalidModel3 = clone $model;
        $invalidModel3->integer = '123';

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
        $invalidModel16->multipleRequiredInteger = '3';

        $invalidModel17 = clone $model;
        $invalidModel17->emailRule = 'invalidEmail';

        $invalidModel18 = clone $model;
        $invalidModel18->multipleRules = 3;

        $invalidModel19 = clone $model;
        $invalidModel19->stringArray = array(3);

        $invalidModel20 = clone $model;
        $invalidModel20->integerArray = array(3,'asd');

        $invalidModel21 = clone $model;
        $invalidModel21->booleanArray = array(true,'asd');

        $invalidModel22 = clone $model;
        $invalidModel22->objectArray = array(new stdClass(),'asd');

        return [
            array($invalidModel1, ''),
            array($invalidModel2, ''),
            array($invalidModel3, ''),
            array($invalidModel4, ''),
            array($invalidModel5, ''),
            array($invalidModel6, ''),
            array($invalidModel7, ''),
            array($invalidModel8, 'requiredString'),
            array($invalidModel9, 'requiredString'),
            array($invalidModel10, 'requiredString'),
            array($invalidModel11, ''),
            array($invalidModel12, ''),
            array($invalidModel13, ''),
            array($invalidModel14, 'requiredInteger'),
            array($invalidModel15, 'requiredInteger'),
            array($invalidModel16, 'requiredInteger'),
            array($invalidModel14, 'testRequired'),
            array($invalidModel15, 'testRequired'),
            array($invalidModel16, 'testRequired'),
            array($invalidModel17, ''),
            array($invalidModel18, ''),
            array($invalidModel19, ''),
            array($invalidModel20, ''),
            array($invalidModel21, ''),
            array($invalidModel22, '')
        ];
    }
}
