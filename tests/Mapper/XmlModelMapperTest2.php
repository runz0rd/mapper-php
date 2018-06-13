<?php

/**
 * Created by PhpStorm.
 * User: somov.nn@gmail.com

 */
use Mapper\XmlModelMapper;
use Common\Util\Xml;


class XmlModelMapperTest2 extends PHPUnit_Framework_TestCase {

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
        $actualModel = $this->xmlMapper->map($xml, new XmlTestUrlSet());
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

        $model = new XmlTestUrlSet();

        $xml = Xml::loadFromFile(__DIR__ . '/../testFiles/valid2.xml');

        return array(
            array($model, $xml)
        );
    }
}
