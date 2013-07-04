<?php

namespace li3_hosting\models;

class DigitalOcean extends Hosting {
    
    protected $_meta = array(
        'connection' => 'digitalocean',
        'key'        => 'id'
    );
    
}

DigitalOcean::finder('servers', function($self, $params, $chain) {
	if (isset($params['options']['conditions']['id'])) {
		$params['options']['path'] = '/servers/{:id}';
	} else {
		$params['options']['path'] = '/servers';
	}

    return $chain->next($self, $params, $chain);
});

DigitalOcean::finder('ssh_keys', function($self, $params, $chain) {
	if (isset($params['options']['conditions']['id'])) {
		$params['options']['path'] = '/ssh_keys/{:id}';
	} else {
		$params['options']['path'] = '/ssh_keys';
	}

    return $chain->next($self, $params, $chain);
});