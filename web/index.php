<?php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Silex\Application();

    $app['debug'] = true;
    
    $app['flickr.api.key'] = 'YOUR FLICKR API KEY GOES HERE';
    $app['flickr.api.secret'] = 'YOUR FLICKR API SECRET GOES HERE';
    $app['flickr.callback.url'] = 'http://localhost/callback';
    
    $app['flickr'] = $app->share(function() use ($app) {

        $client = new \Bert\Flickr\Client($app['flickr.api.key'], $app['flickr.api.secret'], $app['flickr.callback.url']);

        if ($app['session']->has('user')) {
        
            $user = $app['session']->get('user');
            $client->setToken($user['token']);
        
        }
        
        return $client;
        
    });

    $app->register(new Silex\Provider\TwigServiceProvider(), array(
        'twig.path' => __DIR__.'/views',
    ));
    
    $app->register(new Silex\Provider\SessionServiceProvider());

    $app->get('/', function () use ($app) {
    
        if ($app['session']->has('user')) {

            $user = $app['session']->get('user');

            return $app['twig']->render('dashboard.twig.html', array(
                'user' => $user,
            ));
            
        } else {

            return $app['twig']->render('index.twig.html');
            
        }
    
    });
    
    $app->get('/logout', function () use ($app) {
    
        if ($app['session']->has('user')) {
            $app['session']->remove('user');
        }
        
        return $app->redirect('/');
    
    });
    
    
    $app->get('/authorize', function () use ($app) {
        
        $token = $app['flickr']->getRequestToken('http://localhost/callback');
        $app['session']->set('oauth_token_secret', $token['oauth_token_secret']);
        $authorize_url = $app['flickr']->getAuthorizeUrl($token);
        return $app->redirect($authorize_url);

    });

    $app->get('/callback', function () use ($app) {

        $app['flickr']->setToken(array(
            'oauth_token' => $app['request']->get('oauth_token'), 
            'oauth_token_secret' => $app['session']->get('oauth_token_secret')
        ));
        
        $token = $app['flickr']->getAccessToken($app['request']->get('oauth_verifier'));
        
        $app['session']->remove('oauth_token_secret');

        $user = array(
            'token' => $token,
        );
        
        $app['flickr']->setToken($user['token']);
        
        $response = $app['flickr']->callMethod('flickr.people.getInfo', array('user_id' => $user['token']['user_nsid']));
        
        if ($response['stat'] == 'ok') {

            $person = $response['person'];
            
            if ($person['iconserver'] > 0) {
                $person['buddyicon_url'] = 'http://farm' . $person['iconfarm'] . '.staticflickr.com/' . $person['iconserver'] . '/buddyicons/' . $person['nsid'] . '.jpg';
            }
            $user['person'] = $person;

        }
        
        $app['session']->set('user', $user);

        return $app->redirect('/');

    });
    
    $app->run();
