<?php

namespace li3_hosting\models;

class DigitalOcean extends \lithium\data\Model {
    
    protected $_meta = array(
        'connection' => 'hosting',
        'key'        => 'id'
    );
    
}

DigitalOcean::finder('one', function($self, $params, $chain) {
    return $chain->next($self, $params, $chain);
});
