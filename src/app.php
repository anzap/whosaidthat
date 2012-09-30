<?php

use WhoSaidThat\AppInfo;
use WhoSaidThat\Utils;
use WhoSaidThat\DAO;
use WhoSaidThat\domain\User;
use WhoSaidThat\domain\Friend;
use WhoSaidThat\domain\Status;

$app = new Silex\Application();
$app['debug'] = true;
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/templates',
));

$app['pdo.dsn'] = "pgsql:host=".AppInfo::getDbHost().";dbname=".AppInfo::getDbName();


$app['pdo'] = $app->share(function () use ($app) {
    return new PDO(
        $app['pdo.dsn'],
        AppInfo::getDbUser(),
        AppInfo::getDbPass()
    );
});

$app['dao'] = $app->share(function () use ($app) {
    return new DAO($app['pdo']);
});


$facebook = new Facebook(array(
            'appId' => AppInfo::appID(),
            'secret' => AppInfo::appSecret(),
        ));

$user_id = $facebook->getUser();
$basic = array();

if ($user_id) {
  try {
    // Fetch the viewer's basic information
    $basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    throw $e;
  }

  $user = new User(Utils::idx($basic, 'id'), Utils::idx($basic, 'name'));

  $app['dao']->createUser($user);

  $friends = Utils::idx($facebook->api('/me/friends'), 'data', array());

  foreach($friends as $friend) {
    $friendUser = new User(Utils::idx($friend, 'id'), Utils::idx($friend, 'name'));
    $app['dao']->createUser($friendUser);
    $friendObj = new Friend($friendUser, $user);
    $app['dao']->createFriend($friendObj);
  }

  $friend_statuses = $facebook->api(array(
    'method' => 'fql.query',
    'query' => 'SELECT post_id, actor_id, message FROM 
        stream WHERE source_id IN(SELECT uid2 FROM friend WHERE uid1 = me()) and message != "" LIMIT 50'
  ));

  foreach($friend_statuses as $friend_status) {
    $status = new Status(Utils::idx($friend_status, 'post_id'), Utils::idx($friend_status, 'message'),
        new User(Utils::idx($friend_status, 'actor_id')));
    $app['dao']->createStatus($status);
  }

}

// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/' . AppInfo::appID());
$app_name = Utils::idx($app_info, 'name', '');


$app->get('/', function() use ($app, $app_name, $basic) {
            return $app['twig']->render('index.html.twig', 
                array("app_name" => $app_name, "appInfo" => new AppInfo(), 
                    "basic" => $basic, "utils" => new Utils()));
        });

$app->error(function (\Exception $e, $code) {
    return new Response('We are sorry, but something went terribly wrong.');
});

$app->error(function(\Exception $e, $code) use($app) {
    echo 'in error handler';
    if ($app['debug']) {
        return;
    }
    if (!in_array($app['request']->server->get('REMOTE_ADDR'), array('127.0.0.1', '::1'))) {
        return $app->redirect('/');
    }
});


return $app;