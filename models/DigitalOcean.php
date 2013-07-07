<?php

namespace li3_hosting\models;

use lithium\security\Auth;
use lithium\storage\Cache;
use lithium\util\Inflector;

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

		$cacheKey = $userId . '::' . $type . '::' . $id;

		if($result = Cache::read('default', $cacheKey)) {
			return $result;
		} else {
			$result = parent::find($type, $options);

    		Cache::write('default', $cacheKey, $result, $cache);
    		return $result;
		}
    }

    // TODO invalidate cache on update, delete
}

/**
 * Filter DigitalOcean::save to invalidate cache for that request type
 * 
 * @author    alecs popa
 * @since     2013.01.14
 */
DigitalOcean::applyFilter('save', function($self, $params, $chain) {
	if ($params['data']) {
        $params['entity']->set($params['data']);
        $params['data'] = array();
    }

	$user = Auth::check('default');
    $userId = isset($user['id']) ? $user['id'] : '';

    $cacheKey = $userId . '::' . Inflector::pluralize($params['entity']->type) . '::';
    Cache::delete('default', $cacheKey);

	return $chain->next($self, $params, $chain);
});

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