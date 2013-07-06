<?php

namespace li3_hosting\models;

use lithium\security\Auth;
use lithium\storage\Cache;

class DigitalOcean extends Hosting {
    
    protected $_meta = array(
        'connection' => 'digitalocean',
        'key'        => 'id'
    );
    
    public static function find($type, array $options = array()) {
    	$id = isset($options['conditions']['id']) ? $options['conditions']['id'] : '';
    	$cache = isset($options['meta']['cache']) ? $options['meta']['cache'] : '+30 day';

    	$user = Auth::check('default');
    	$userId = isset($user['id']) ? $user['id'] : '';

		$cacheKey = $type . '::' . $id . '::' . $userId;

		if($result = Cache::read('default', $cacheKey)) {
			return self::create($result, array('exists' => true));
		} else {
			$result = parent::find($type, $options);
 
    		Cache::write('default', $cacheKey, $result->to('array'), $cache);
    		return $result;
		}
    }
}

DigitalOcean::finder('servers', function($self, $params, $chain) {
	if (isset($params['options']['conditions']['id'])) {
		$params['options']['path'] = '/servers/{:id}';
	} else {
		$params['options']['path'] = '/servers';
	}

	$params['meta']['cacahe'] = '+2 hours';

    return $chain->next($self, $params, $chain);
});

DigitalOcean::finder('ssh_keys', function($self, $params, $chain) {
	if (isset($params['options']['conditions']['id'])) {
		$params['options']['path'] = '/ssh_keys/{:id}';
	} else {
		$params['options']['path'] = '/ssh_keys';
	}

	$params['meta']['cacahe'] = '+15 minutes';

    return $chain->next($self, $params, $chain);
});

DigitalOcean::finder('sizes', function($self, $params, $chain) {
	$params['options']['path'] = '/sizes';
    return $chain->next($self, $params, $chain);
});