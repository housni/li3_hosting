<?php

namespace li3_hosting\extensions\adapter\data\source\http;

/**
 * Gateway Hosting API data source for Lithium.
 */
abstract class Hosting extends \lithium\data\source\Http {

	protected static $_user = '';
    protected static $_pass = '';

	/**
     * Fully-name-spaced class references to `Hosting` class dependencies.
     *
     * @var array
     */
    protected $_classes = array(
        'service'      => 'lithium\net\http\Service',
        'entity'       => 'lithium\data\entity\Document',
        'set'          => 'lithium\data\collection\DocumentSet',
        'schema'       => 'lithium\data\DocumentSchema'
    );

	public function create($query, array $options = array()) {

	}

	public function read($query, array $options = array()) {

	}

	public function update($query, array $options = array()) {

	}

	public function delete($query, array $options = array()) {

	}

	public static function getCredentials() {
		return array('user' => self::$_user, 'pass' => self::$_pass);
	}

}