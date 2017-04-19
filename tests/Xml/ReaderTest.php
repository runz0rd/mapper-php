<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 11/03/17
 * Time: 11:36
 */

use Xml\Reader;

class ReaderTest extends PHPUnit_Framework_TestCase
{
    public function testParser() {
        $xml = \Common\Util\Xml::loadFromFile(__DIR__ . '/../Mapper/xml/valid_testModel.xml');
        $reader = new Reader($xml);
        $asd = $reader->read();
    }
}
