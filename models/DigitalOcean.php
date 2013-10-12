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
    	$self = static::_object();

    	$id = isset($options['conditions']['id']) ? $options['conditions']['id'] : '';
    	$cache = isset($options['meta']['cache']) ? $options['meta']['cache'] : '+30 day';

    	if ($cache) {
    		$conn = $self::connection();
    		$credentials = $conn::getCredentials();
    		$cacheKey = $credentials['user'] . '::' . $credentials['pass'] . '::' . $type . '::' . $id;

    		if($result = Cache::read('default', $cacheKey)) {
				return $result;
			} else {
				// read from API and save the result in cache
				$result = parent::find($type, $options);

	    		Cache::write('default', $cacheKey, $result, $cache);
	    		return $result;
			}
    	} else {
    		// just read from API and dont save the result into cache
    		return parent::find($type, $options);
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

    // clear cache for collection
    $cacheKey = $userId . '::' . Inflector::pluralize($params['entity']->type) . '::';
    Cache::delete('default', $cacheKey);

    // clear cache for entity
    if ($params['entity']->id) {
    	$cacheKey = $userId . '::' . Inflector::pluralize($params['entity']->type) . '::' . $params['entity']->id;
    	Cache::delete('default', $cacheKey);
    }

	return $chain->next($self, $params, $chain);
});

DigitalOcean::finder('servers', function($self, $params, $chain) {
	if (isset($params['options']['conditions']['id'])) {
		$params['options']['path'] = '/servers/{:id}';
	} else {
		$params['options']['path'] = '/servers';
	}

	$params['meta']['cache'] = '+2 hours';

    return $chain->next($self, $params, $chain);
});

DigitalOcean::finder('ssh_keys', function($self, $params, $chain) {
	if (isset($params['options']['conditions']['id'])) {
		$params['options']['path'] = '/ssh_keys/{:id}';
	} else {
		$params['options']['path'] = '/ssh_keys';
	}

	$params['meta']['cache'] = '+15 minutes';

    return $chain->next($self, $params, $chain);
});

DigitalOcean::finder('sizes', function($self, $params, $chain) {
	$params['options']['path'] = '/sizes';
    return $chain->next($self, $params, $chain);
});
