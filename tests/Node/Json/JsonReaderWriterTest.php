<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 11/03/17
 * Time: 11:36
 */

class JsonReaderWriterTest extends PHPUnit_Framework_TestCase
{
    public function testReadAndWrite() {
        $expected = \Common\Util\File::read(__DIR__ . '/../../testFiles/valid.json');
        $reader = new \Node\Json\Reader();
        $writer = new \Node\Json\Writer();
        $node = $reader->read($expected);
        $actual = $writer->write($node);
        $this->assertEquals(json_decode($expected), json_decode($actual));
    }
}