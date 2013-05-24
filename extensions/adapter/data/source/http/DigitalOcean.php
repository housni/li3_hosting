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
        'read' => array(
        	'/droplets'       => array(),
        	'/droplets/{:id}' => array('id'),
        	'/droplets/new'   => array(),
        	'/regions'        => array(),
        	'/images'         => array(),
        	'/sizes'          => array(),
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

    public function read($query, array $options = array()) {
        $data = $this->_send('get', $options);

        pr($data); die('__a__');


        /*
        $defaults = array('return' => 'resource', 'model' => $query->model());
        $options += $defaults;
        $params = compact('query', 'options');
        $conn =& $this->connection;
        $config = $this->_config;

        return $this->_filter(__METHOD__, $params, function($self, $params) use (&$conn, $config) {
            $query = $params['query'];
            $options = $params['options'];
            $params = $query->export($self);
            extract($params, EXTR_OVERWRITE);
            list($_path, $conditions) = (array) $conditions;
            $model = $query->model();

            if (empty($_path)) {
                $_path = '_all_docs';
                $conditions['include_docs'] = 'true';
            }
            $path = "{$config['database']}/{$_path}";
            $args = (array) $conditions + (array) $limit + (array) $order;
            $result = $conn->get($path, $args);
            $result = is_string($result) ? json_decode($result, true) : $result;
            $data = $stats = array();

            if (isset($result['_id'])) {
                $data = array($result);
            } elseif (isset($result['rows'])) {
                $data = $result['rows'];
                unset($result['rows']);
                $stats = $result;
            }
            $stats += array('total_rows' => null, 'offset' => null);
            $opts = compact('stats') + array('class' => 'set', 'exists' => true);
            return $self->item($query->model(), $data, $opts);
        });
        /**/
    }

    /**
     * Map generic api query paths to this API adapter path
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
                throw new QueryException("Api query path {$path} is not supported.");
        }
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
        $path = $this->_mapPath($path);

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
    protected function _send($method, array $params = array()) {
        if (!isset($params['path'])) {
            throw new QueryException('Api query path is missing.');
        }

        $path = $this->_path($params['type'], $params['path'], (array) $params['conditions']);

        $conn =& $this->connection;
        $config = $this->_config;

        // add DigitalOcean API credentials for auth
        $data = array('client_id' => $this->_clientId, 'api_key' => $this->_apiKey);

        $result = $conn->get($path, $data);
        $result = is_string($result) ? json_decode($result, true) : $result;

        if (isset($result['status']) && $result['status'] == 'OK') {
            return $result;
        } else {
            throw new QueryException('Server responded with: ' . $result['status']);
        }

        /*
        $path        = $this->_path($type, $data);
        $service     = $this->_instance($this->_classes['service'], $this->config['host']);
pr('_service_', $service); die;
        if (!$path) {
            throw new QueryException('Unknown request type');
        }
        
        if (isset($options['headers'])) {
            $options['headers']['X-Auth-Token'] = $credentials['token'];
        } else {
            $options['headers'] = array('X-Auth-Token' => $credentials['token']);
        }

        if (isset($data['type'])) {
            $options['type']                    = $data['type'];
            $options['headers']['Content-type'] = $data['type'];
            $data                               = $data['content'];
        }

        if (is_string($data)) {
            $options['headers']['Content-Length'] = mb_strlen($data);
        }

        if (isset($options['data']) && is_array($options['data']) && is_array($data)) {
            $data += $options['data'];
        }

        $service->send($method, $path, $data, $options);

        $status = $service->last->response->status;

        if ($status['code'] >= 400) {
            throw new QueryException('Could not process request: ' . $status['message'], $status['code']);
        }

        return $service->last->response;
        /**/
    }

}