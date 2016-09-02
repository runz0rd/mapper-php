<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 5/31/2016
 * Time: 2:42 PM
 */

namespace Traits;

use Validator\ModelValidator;

trait ValidatableTrait {

	/**
	 * @param string $validationType
	 */
	public function validate(string $validationType = '') {
		$validator = new ModelValidator();
		$validator->validate($this, $validationType);
	}
}