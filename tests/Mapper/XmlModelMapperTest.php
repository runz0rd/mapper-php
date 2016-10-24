<?php

/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 8/9/2016
 * Time: 8:02 PM
 */
use Mapper\XmlModelMapper;
use Common\Util\Xml;

class XmlModelMapperTest extends PHPUnit_Framework_TestCase {

    /**
     * @var XmlModelMapper
     */
    public $xmlMapper;

    public function setUp() {
        $this->xmlMapper = new XmlModelMapper();
        parent::setUp();
    }

    /**
     * @param $expectedModel
     * @param $xml
     * @dataProvider validValues
     */
    public function testMap($expectedModel, $xml) {
        $actualModel = $this->xmlMapper->map($xml, new TestModel());
        $this->assertEquals($expectedModel, $actualModel);
    }

    /**
     * @param $model
     * @param $expectedXml
     * @dataProvider validValues
     */
    public function testUnmap($model, $expectedXml) {
        $actualXml = $this->xmlMapper->unmap($model);
        $this->assertEquals($expectedXml, $actualXml);
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
        $model->array = [1,'a',3];
        $model->stringArray = ['a','b','c'];
        $model->integerArray = [3];
        $model->booleanArray = [true,true,false];
        $model->objectArray = [$object,$object,$object];
        $model->object = $object;
        $model->requiredString = 'requiredString';
        $model->alwaysRequiredBoolean = false;
        $model->multipleRequiredInteger = 5;
        $nestedModel = new NestedTestModel();
        $nestedModel->mapFromObject($model);
        $nestedModel->attribute1 = 'attribute1';
        $model->model = $nestedModel;
        $model->modelArray = [$nestedModel,$nestedModel];
        $model->xml = new XmlTestModel();
        $model->xml->attributeTest = 'attribute';
        $model->xml->value = 'nodeValue';

        $xml = Xml::loadFromFile(__DIR__ . '/xml/valid_testModel.xml');

        return [
            [$model, $xml]
        ];
    }
}
