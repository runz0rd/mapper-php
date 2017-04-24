<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 11/03/17
 * Time: 11:36
 */

class XmlReaderWriterTest extends PHPUnit_Framework_TestCase
{
    public function testReadAndWrite() {
        $expected = \Common\Util\Xml::loadFromFile(__DIR__ . '/../../testFiles/valid.xml');
        $reader = new Node\Xml\Reader();
        $writer = new \Node\Xml\Writer();
        $node = $reader->read($expected);
        $actual = str_replace("\n", '', $writer->write($node));
        $this->assertEquals($expected, $actual);
    }
}
