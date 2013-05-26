li3_hosting
===========

Lithium Framework gateway extension for comunicating with different hosting APIs (Digital Ocean)

# Usage

Add a connection

		Connections::add('digitalocean', array(
			'type'     => 'http', // required so Lithium knows where to look for the adapter
			'adapter'  => 'DigitalOcean',
			'login'    => '<your API client key here>',
			'password' => '<your API secret key here>'
		));
		

Listing all Digital Ocean servers

		$do = DigitalOcean::servers();
		foreach ($do as $d) {
			print_r($d);
		}
		

Create a server on Digital Ocean

		$do = DigitalOcean::create(array(
			'type'      => 'server',
			'name'      => 'test-01',
			'size_id'   => 66, // 512MB
			'image_id'  => 2676, // Ubuntu 12.04 x64 Server
			'region_id' => 1
		));
		$do->save();

