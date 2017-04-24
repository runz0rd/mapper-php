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

        $xml = Xml::loadFromFile(__DIR__ . '/../testFiles/valid.xml');

        return array(
            array($model, $xml)
        );
    }
}
