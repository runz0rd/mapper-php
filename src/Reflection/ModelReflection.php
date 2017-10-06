<?php

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 29/05/17
 * Time: 21:02
 */
namespace Reflection;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;

class ModelReflection {

    /**
     * @var object
     */
    protected $classInstance;

    /**
     * @var \ReflectionClass
     */
    protected $reflectionClass;

    /**
     * @var Reader
     */
    protected $annotationReader;

    /**
     * ModelReflection constructor.
     * @param object $instance
     */
    public function __construct($instance) {
        AnnotationRegistry::registerLoader('class_exists');
        $this->classInstance = $instance;
        $this->reflectionClass = new \ReflectionClass($instance);
        $this->annotationReader = new AnnotationReader();
        $this->annotationReader::addGlobalIgnoredName('required');
        $this->annotationReader::addGlobalIgnoredName('rule');
        $this->annotationReader::addGlobalIgnoredName('name');
        $this->annotationReader::addGlobalIgnoredName('xmlAttribute');
        $this->annotationReader::addGlobalIgnoredName('xmlNodeValue');
        $this->annotationReader::addGlobalIgnoredName('var');
    }

    public function getPropertyNames() {
        $properties = $this->reflectionClass->getProperties();
        $propertyList = [];
        foreach ($properties as $property) {
            $propertyList[] = $property->getName();
        }
        return $propertyList;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getProperty($name) {
        return $this->getReflectionProperty($name)->getValue($this->classInstance);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setProperty($name, $value) {
        $this->getReflectionProperty($name)->setValue($this->classInstance, $value);
    }

    /**
     * @param string $propertyName
     * @param string $annotationName
     * @return null|object
     */
    public function getPropertyAnnotation($propertyName, $annotationName) {
        return $this->annotationReader->getPropertyAnnotation($this->getReflectionProperty($propertyName), $annotationName);
    }

    /**
     * @param string $propertyName
     * @return array
     */
    public function getPropertyAnnotations($propertyName) {
        return $this->annotationReader->getPropertyAnnotations($this->getReflectionProperty($propertyName));
    }

    /**
     * @param string $name
     * @return \ReflectionProperty
     */
    protected function getReflectionProperty($name) {
        return $this->reflectionClass->getProperty($name);
    }

    /**
     * @return array
     */
    public function getUseStatements() {
        $namespace = $this->reflectionClass->getNamespaceName();
        $classFile = $this->reflectionClass->getFileName();
        $aliases = array();
        if ($classFile !== false) {
            $tokenParser = new TokenParser(file_get_contents($classFile));
            $aliases = $tokenParser->parseUseStatements($namespace);
        }
        return $aliases;
    }

    /**
     * @param string $name
     * @return string
     * @throws \Exception
     */
    public function getUseStatement($name) {
        $aliases = $this->getUseStatements();
        if(!isset($aliases[strtolower($name)])) {
            throw new \Exception('Use statement named "' . $name . '" not found.');
        }
        return $aliases[strtolower($name)];
    }

    /**
     * @param string $name
     * @return string
     * @throws \Exception
     */
    public function getFullClassName($name) {
        $className = $name;
        if(!class_exists($name)) {
            $className = $this->getUseStatement($name);
        }
        if(!class_exists($className)) {
            throw new \Exception('Class ' . $className . ' does not exist.');
        }
        return $className;
    }

    /**
     * @param string $name
     * @return object
     */
    public static function instantiate($name) {
        try {
            $reflectionClass = new \ReflectionClass($name);
            $model = $reflectionClass->newInstanceWithoutConstructor();
        }
        catch(\Exception $ex) {
            throw new \InvalidArgumentException('Could not instantiate model ' . $name . '. ' . $ex->getMessage());
        }
        return $model;
    }
}