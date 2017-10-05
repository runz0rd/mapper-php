<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 29/05/17
 * Time: 21:25
 */

use Reflection\ModelReflection;

class ModelReflectionTest extends PHPUnit_Framework_TestCase
{

    public function testGetPropertyAnnotation() {
        $modelReflection = new ModelReflection(new TestModel());
        $annotation = $modelReflection->getPropertyAnnotation('namedString', Annotation\Name::class);
        $annotations = $modelReflection->getPropertyAnnotations('namedString');
        $aliases = $modelReflection->getAliases();
        $asd = 'asd';
    }
}
