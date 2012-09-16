<?php

use WhoSaidThat\AppInfo;
use WhoSaidThat\Utils;

$app = new Silex\Application();
$app['debug'] = true;
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/templates',
));

$facebook = new Facebook(array(
            'appId' => AppInfo::appID(),
            'secret' => AppInfo::appSecret(),
        ));

// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/' . AppInfo::appID());
$app_name = Utils::idx($app_info, 'name', '');

$app->get('/', function() use ($app, $app_name) {
            return $app['twig']->render('index.html.twig', array("app_name" => $app_name, "appInfo" => new AppInfo()));
        });

$app->error(function(Exception $e) use($app) {
            if (!in_array($app['request']->server->get('REMOTE_ADDR'), array('127.0.0.1', '::1'))) {
                return $app->redirect('/');
            }
        });


return $app;