<?php

namespace li3_hosting\models;

use lithium\data\model\Query;

class DigitalOcean extends \lithium\data\Model {
    
    protected $_meta = array(
        'connection' => 'hosting',
        'source'     => 'digitalocean',
        'key'        => 'id'
    );
    
}

DigitalOcean::finder('one', function($self, $params, $chain) {
    return $chain->next($self, $params, $chain);
});