<?php

namespace li3_hosting\extensions\adapter\data\source\http;

use lithium\data\model\QueryException;
use lithium\util\String;

class DigitalOcean extends Hosting {

    protected $_clientId = '';
    protected $_apiKey = '';

	/**
     * Map of actions to URI path and parameters.
     *
     * @var array 
     */
	protected $_sources = array(
        'create' => array(
            '/droplets/new'   => array(),
        ),
        'read' => array(
        	'/droplets'       => array(),
        	'/droplets/{:id}' => array('id'),
        	'/sizes'          => array(),
            '/ssh_keys'       => array(),
            '/ssh_keys/{:id}' => array('id'),
        ),
	);

    /**
     * Map responses to objects
     */
    protected $_mappings = array(
        'create' => array(
            '/droplets/new'   => array('class' => 'entity', 'data' => 'droplet'),
        ),
        'read' => array(
            '/droplets'       => array('class' => 'set',    'data' => 'droplets'),
            '/droplets/{:id}' => array('class' => 'entity', 'data' => 'droplet'),
            '/sizes'          => array('class' => 'set',    'data' => 'sizes'),
            '/ssh_keys'       => array('class' => 'set',    'data' => 'ssh_keys'),
            '/ssh_keys/{:id}' => array('class' => 'entity', 'data' => 'ssh_keys'),
        ),
    );

	/**
     * Initializes a new `DigitalOcean` instance with the default settings.
     * 
     * @param array $config Class configuration
     */
    public function __construct(array $config = array()) {
        $defaults = array(
            'type'     => 'http',
            'scheme'   => 'https',
            'port'     => 443,
            'host'     => 'api.digitalocean.com',
            'auth'      => null,
            'login'     => '', // client_id
            'password'  => '', // api_key
        );

        $options = $config + $defaults;
        $this->_clientId = $options['login'];
        $this->_apiKey = $options['password'];

        // unset these two otherwise will be used in http auth later down the rabbit hole
        $options['login'] = '';
        $options['password'] = '';

        parent::__construct($options);
    }

    /**
     * Map generic API query paths to this API adapter path
     *
     * @param string $path
     * @return string path converted for this API adapter
     */
    protected function _mapPath($path) {
        switch ($path) {
            case '/servers':
                return '/droplets';
            case '/servers/{:id}':
                return '/droplets/{:id}';
            default:
                return $path;
        }
    }

    /**
     * Map API response to data type and get data container
     *
     * @param string method
     * @param string $path
     * @return array data type and data container
     */
    protected function _mapResponse($method, $path) {
        if(isset($this->_mappings[$method][$path])) {
            return $this->_mappings[$method][$path];
        }
        
        return null;
    }

    /**
     * Maps action/parameters to the URI path to be used in the request.
     * 
     * @param string $type Action being performed (`create`, `read`, `update` or `delete).
     * @param string $path Action path
     * @param array $params Action parameters.
     * 
     * @return string URI path to be used in the request.
     */
    protected function _path($type, $path, array $params = array()) {
        if (!isset($this->_sources[$type]) || !isset($this->_sources[$type][$path])) {
            return null;
        }

        return String::insert($path, $params);
    }

    /**
     * Sends a request and returns the response object.
     * 
     * @param type $method HTTP method.
     * @param array $params
     * 
     * @return string json
     */
    protected function _send($method, $query, array $params = array()) {
        $path = $this->_path($params['type'], $params['path'], (array) $params['conditions']);

        $conn =& $this->connection;
        $config = $this->_config;

        // add DigitalOcean API credentials for auth
        $data = isset($params['data']) ? (array) $params['data'] : array();
        $data += array('client_id' => $this->_clientId, 'api_key' => $this->_apiKey);

        $result = $conn->get($path, $data);
        $result = is_string($result) ? json_decode($result, true) : $result;

        if (isset($result['status']) && $result['status'] == 'OK') {
            // get reponse mappings to objects and data container from response
            $mapping = $this->_mapResponse($params['type'], $params['path']);

            $itemData = isset($params['data']) ? (array) $params['data'] : array();
            $itemData += $result[$mapping['data']];

            $opts = array('class' => $mapping['class'], 'exists' => true);

            return $this->item($query->model(), $itemData, $opts);
        } else {
            throw new QueryException('Server responded with: ' . $result['status']);
        }
    }

    /**
     * Create entity using API
     */
    public function create($query, array $options = array()) {
        $entity =& $query->entity();

        $params['type'] = __FUNCTION__;
        $params['data'] = (array) $query->data();

        $params['path'] = '';
        if (isset($params['data']['type']) && $params['data']['type'] == 'server') {
            $params['path'] = key($this->_sources[__FUNCTION__]);
        }
        unset($params['data']['type']);
        $params['conditions'] = array();

        // call API
        $item = $this->_send('get', $query, $params);

        if ($entity) {
            $entity->sync($item->id);
        }

        return true;
    }

    /**
     * Read data from API server
     */
    public function read($query, array $options = array()) {
        if (!isset($options['path'])) {
            throw new QueryException('Api query path is missing.');
        }

        $options['path'] = $this->_mapPath($options['path']);

        // call API
        return $this->_send('get', $query, $options);
    }
}