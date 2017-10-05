<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 17/09/17
 * Time: 10:43
 */

class ExceptionDecorator
{
    private $decoratedService;

    private $exceptionHandler;

    /**
     * ExceptionDecorator constructor.
     * @param $decoratedService
     * @param $exceptionHandler
     */
    public function __construct($decoratedService, $exceptionHandler)
    {
        $this->decoratedService = $decoratedService;
        $this->exceptionHandler = $exceptionHandler;
    }


    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments) {
        if(!method_exists($this->decoratedService, $name)) {
            throw new \Exception();
        }
        try {
            $result = call_user_func_array([$this->decoratedService, $name], $arguments);
        } catch(\Exception $ex) {
            $this->exceptionHandler->handle($ex);
        }
        return $result;
    }
}