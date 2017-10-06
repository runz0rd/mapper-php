<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 7/7/2016
 * Time: 10:56 AM
 */

namespace Mapper;


interface IModelMapper {

	/**
	 * @param mixed $source
	 * @param object $model
	 * @return object
	 */
	function map($source, $model);

	/**
	 * @param object $model
	 * @return mixed
	 */
	function unmap($model);
}