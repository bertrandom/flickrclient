# flickrclient

flickrclient is simple PHP client for the Flickr API with OAuth support.

It is intended for quick Flickr hacks where you need a user to authorize with Flickr.

After authing, it mimics [flickr.simple.php](https://github.com/kellan/flickr.simple.php) where all Flickr API calls are made through callMethod(), passing the params you want in an array and getting the raw Flickr PHP array response in return.

## Usage

The OAuth authorization process has token exchanges and callbacks so the best way to understand how to use the library is to look at the demo, which has routes for the authorization and callback. Once you have gotten an oauth_token and oauth_token_secret for the user, this is an example of how to make an API call:

	$client = new \Bert\Flickr\Client('YOUR FLICKR API KEY GOES HERE', 'YOUR FLICKR API SECRET GOES HERE', 'http://localhost/callback');
	
	$client->setToken(array(
		'oauth_token' => 'oauth_token stored for the user',
		'oauth_token_secret' => 'oauth_token_secret stored for the user',
	));
	
	$response = $app['flickr']->callMethod('flickr.people.getInfo', array('user_id' => '61091860@N00'));
        
	if ($response['stat'] == 'ok') {
	
	    $person = $response['person'];
	    
		print_r($person);
	
	}


## Demo

I've created a simple demo of a user signing in with Flickr and authorizing their account to demonstrate how to use flickrclient. If you would like to browse the source, simply switch to the `demo` branch of flickrclient. The core of the demo is in `web/index.php`. It uses the [Silex](http://silex.sensiolabs.org/) microframework.

### Installation

	mkdir flickrclientdemo
	cd flickrclientdemo
	git clone -b demo git@github.com:bertrandom/flickrclient.git .
	curl -s http://getcomposer.org/installer | php
	php composer.phar install
	
### Configuration

Edit `web/index.php` and set your API key, secret, and callback URL:

	$app['flickr.api.key'] = 'YOUR FLICKR API KEY GOES HERE';
	$app['flickr.api.secret'] = 'YOUR FLICKR API SECRET GOES HERE';
	$app['flickr.callback.url'] = 'http://localhost/callback';

Point Apache to `web/` and `AllowOverride All` for the directory so that it can read the .htaccess file. Then point your browser to `http://localhost/` and try the demo.