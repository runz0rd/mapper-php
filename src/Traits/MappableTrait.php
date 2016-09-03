<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 5/31/2016
 * Time: 2:42 PM
 */

namespace Traits;
use Mapper\ModelMapperException;
use Mapper\ModelMapper;
use Common\Util\Validation;
use Mapper\XmlModelMapper;

trait MappableTrait {

	/**
	 * @param array $data
	 * @throws \InvalidArgumentException
	 * @throws ModelMapperException
	 */
	public function mapFromArray(array $data) {
		$json = json_encode($data);
        if($json === false) {
            throw new \InvalidArgumentException('Invalid array supplied.');
        }
		$object = json_decode($json);
        if($object === null) {
            throw new \InvalidArgumentException('Invalid array supplied.');
        }

		$this->mapFromObject($object);
	}

	/**
	 * @param string $data
	 * @throws \InvalidArgumentException
	 * @throws ModelMapperException
	 */
	public function mapFromJson(string $data) {
		$object = json_decode($data);
        if($object === null) {
            throw new \InvalidArgumentException('Invalid json supplied.');
        }

		$this->mapFromObject($object);
	}

	/**
	 * @param $object
	 */
	public function mapFromObject($object) {
		$mapper = new ModelMapper();
		$mapper->map($object, $this);
	}

    /**
     * @param string $xml
     */
    public function mapFromXml(string $xml) {
        $mapper = new XmlModelMapper();
        $mapper->map($xml, $this);
    }
}