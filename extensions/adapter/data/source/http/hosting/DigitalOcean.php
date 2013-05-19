<?php

namespace li3_hosting\extensions\adapter\data\source\http\hosting;

use lithium\data\model\QueryException;

class DigitalOcean extends \li3_hosting\extensions\adapter\data\source\http\Hosting {

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
            'type'      => 'http',
            'host'      => 'api.digitalocean.com',
            'port'      => 443,
            'scheme'    => 'https',
            'auth'      => null,
            'login'     => null, // client_id
            'password'  => null, // api_key
        );

        parent::__construct($config + $defaults);
    }

    public function read($query, array $options = array()) {
		$params     = array('data' => array('format' => 'json'));
        $method     = 'GET';
        
        extract($query->export($this, array('conditions')));

        if (!$conditions) {
            $conditions = array();
        }

        $model    = $query->model();
        $response = $this->_send(__FUNCTION__, $method, $conditions, $params);

        pr($response); die;
    }

    /**
     * Sends a request and returns the response object.
     * 
     * @param type $type Request type (`create`, `read`, `update`, `delete)
     * @param type $method HTTP method.
     * @param type $data Request data.
     * @param array $options Additional request options (eg. `headers`).
     * 
     * @return object Instance of net\http\Response.
     */
    protected function _send($type, $method, $data, array $options = array()) {
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
    }

}