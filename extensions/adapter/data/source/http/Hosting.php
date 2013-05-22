<?php

namespace li3_hosting\extensions\adapter\data\source\http;

use lithium\util\String;

/**
 * Hosting API data source for Lithium.
 */
abstract class Hosting extends \lithium\data\source\Http {

	/**
     * Map of actions to URI path and parameters.
     *
     * @var array 
     */
    protected $_sources = array();

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

    /**
     * Maps action/parameters to the URI path to be used in the request.
     * 
     * @param string $type Action being performed (`create`, `read`, `update` or `delete).
     * @param array $params Action parameters.
     * 
     * @return string URI path to be used in the request.
     */
    protected function _path($type, array $params = array()) {
        if (!isset($this->_sources[$type])) {
            return null;
        }
        
        // if there is only one possible path for this request type
        if (!is_array($this->_sources[$type])) {
            return String::insert($this->_sources[$type], array_map('urlencode', $params) + $this->_config);
        }
        
        $path = null;        
        $keys = array_keys($params);
        sort($keys);

        foreach ($this->_sources[$type] as $sourcePath => $sourceParams) {

            sort($sourceParams);

            if ($sourceParams === $keys) {
                $path = String::insert($sourcePath, array_map('urlencode', $params) + $this->_config);
                break;
            }            
        }

        return $path;
    }

}