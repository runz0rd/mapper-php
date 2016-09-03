# PHP Model Mapper

## Feature list:
 * Map PHP class properties from json, xml, arrays and other objects.
 * Unmap from model to json, xml, array and stdClass
 * Nested models supported
 * Annotations provide custom naming, types and rules for model properties
 * Properly annotated, mapped models can be validated for different types and rules

## Requirements:
  * PHP 7.0.1+

## Installation
```
composer require runz0rd/mapper-php
```

## Annotations
Heres a list of annotations we can use in models:

| annotation   | used above  | description              |
| :----------- | :---------- | :----------------------- |
| @root        |  class      | Used to name the root of the element (XML) |
| @var         |  property   | Used to declare the type of the property (validation). This is an alias for @rule annotation. See below for a list of pre-defined types (rules) for model validation |
| @name        |  property   | Used to change the property name, if necessary (can be used with special characters, but be careful if youre working with XML) |
| @required    |  property   | Used to declare that a property is required for a specific action (validation). Use without the action to make the property always required |
| @attribute   |  property   | Used to declare that a property is an attribute for the element (XML) |
| @rule        |  property   | Used to enforce specific rules and filters on the property value (validation). You can use the predefined rules or create and import your own |

**@var** annotation type list:

| type      |  description              |
| :-------- | :------------------------ |
| string    | string type               |
| integer   | integer type              |
| boolean   | boolean type              |
| array     | array type                |
| []        | array type                |
| object    | stdClass for example      |
| string[]  | string array type         |
| integer[] | integer array type        |
| boolean[] | boolean array type        |
| object[]  | object array type         |
| NamedModel | any named class (model) (if from another namespace, make sure you include it!) |
| NamedModel[] | any model array         |

## Usage

### Mapping

Use MappableTrait inside your model to call methods from the model itself:
```PHP
$model->mapFromArray($myArray);
$model->mapFromJson($myJson);
$model->mapFromXml($myXml);
$model->mapFromObject($myObject);
```

Or by using the ModelMapper class, providing it with the instance of the model you want mapped and the source object:
```PHP
$model = new Model();
$mapper = new ModelMapper();
$mapper->map($sourceObject, $model);
```

### Unmapping

Use ConvertibleTrait inside your model to call methods from the model itself:
```PHP
$myArray = $model->toArray();
$myJson = $model->toJson();
$myXml = $model->toXml();
$myObject = $model->toObject();
```

Or by using the ModelMapper class, providing it with the mapped model (converts to stdClass):
```PHP
$mapper = new ModelMapper();
$myObject = $mapper->unmap($myModel);
```

### Validation
Validation will check your mapped model's property types, custom rules, and whether the property is required or not.

Validate your mapped models with ValidatableTrait, by providing it with the desired validation action:

```PHP
$model->validate('createAction');
```
Or if you want to validate only the properties that are always required:
```PHP
$model->validate();
```
You can also use the validator itself:
```PHP
$model = new Model();
$validator = new ModelValidator();
$validator->validate($model, 'myAction');
```

### Rules
Rules are used for custom validation of mapped property values.

```PHP
/**
 * @rule email
 */
public $email;
```
This would check if the property value contains a valid email string.

You can pass additional parameters for rules.
```PHP
/**
 * @rule limit(0,99)
 */
public $value;
```
If setup properly this custom rule would check if the property value is between 0 and 99.

### Rule setup
Lets use the limit rule example from above, to create a new custom rule:
```PHP
use Common\Models\ModelProperty;
use Validator\IRule;
use Validator\ModelValidatorException;

class LimitRule implements IRule {

    function getNames() {
        return ['limit'];
    }

    function validate(ModelProperty $property, array $params = []) {
        if($property->getPropertyValue() < $params[0] || $property->getPropertyValue() > $params[1]) {
            throw new ModelValidatorException('Value is not between '.$params[0].' and '.$params[1]);
        }
    }
}
```
In the example above, we can configure a new rule, by:
 * Defining an array of names (aliases) which serve as the name of the rule in the annotation (getNames)
 * Providing a validation definition and throwing an exception if it doesnt pass (validate)
 * The rule parameters (0,99) come in through the $params array, in order in which they were provided

For more information please take a look at the pre-defined rule classes and the IRule interface.

### Loading your rules 
```PHP
$model = new Model();
$myCustomRule = new MyCustomRule();
$validator = new ModelValidator();
$validator->useRule($myCustomRule);
$validator->loadRules('/path/to/rules/');
$validator->validate($model, 'myAction');
```
In the example above we can use custom rules by providing them to the validator one by one (useRule) or providing a path to a directory containing rules (loadRules).

## Code examples
Please see the tests and models used in it.

## Stuff that got me inspired:
[cweiske/jsonmapper](https://github.com/cweiske/jsonmapper)
