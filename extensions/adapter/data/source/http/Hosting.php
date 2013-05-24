<?php

namespace li3_hosting\extensions\adapter\data\source\http;

/**
 * Gateway Hosting API data source for Lithium.
 */
abstract class Hosting extends \lithium\data\source\Http {

	/**
     * Fully-name-spaced class references to `Hosting` class dependencies.
     *
     * @var array
     */
    protected $_classes = array(
        'service'      => 'lithium\net\http\Service',
        'entity'       => 'lithium\data\entity\Document',
        'set'          => 'lithium\data\collection\DocumentArray',
        'schema'       => 'lithium\data\DocumentSchema',
    );

	public function create($query, array $options = array()) {

	}

	public function read($query, array $options = array()) {

	}

	public function update($query, array $options = array()) {

	}

	public function delete($query, array $options = array()) {

	}

}