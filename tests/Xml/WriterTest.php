<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 11/03/17
 * Time: 11:36
 */

class WriterTest extends PHPUnit_Framework_TestCase
{

    public function testWriter() {
        $xml = \Common\Util\Xml::loadFromFile(__DIR__ . '/../Mapper/xml/valid_testModel.xml');
        $reader = new \Xml\Reader($xml);
        $element = $reader->read();

        $writer = new Xml\Writer();
        $xmlOutput = $writer->write($element);
        $cleanXml = str_replace("\n", '', $xmlOutput);
        $this->assertEquals($xml, $cleanXml);
    }

}
