<?php

use WhoSaidThat\AppInfo;
use WhoSaidThat\Utils;
use WhoSaidThat\DAO;
use WhoSaidThat\Level;
use WhoSaidThat\domain\User;
use WhoSaidThat\domain\Friend;
use WhoSaidThat\domain\Status;
use Symfony\Component\HttpFoundation\Request;
use WhoSaidThat\utils\exceptions\LevelSelectionException;

$app = new Silex\Application();
$app['debug'] = true;
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/templates',
));
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app['pdo.dsn'] = "pgsql:host=".AppInfo::getDbHost().";dbname=".AppInfo::getDbName();


$app['pdo'] = $app->share(function () use ($app) {
    $pdo = new PDO(
        $app['pdo.dsn'],
        AppInfo::getDbUser(),
        AppInfo::getDbPass()
    );
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
});

$app['dao'] = $app->share(function () use ($app) {
    return new DAO($app['pdo']);
});

$answer_time = time();


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

  $user = $app['dao']->findUser(Utils::idx($basic, 'id'));
  if(!$user) {
    $user = new User(Utils::idx($basic, 'id'), Utils::idx($basic, 'name'));
    $app['dao']->createUser($user);
  }

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

$app->post('/level', function(Request $request) use ($app, $user) {
  if($request->get('level')) {
    $app['session']->set('level', new Level($request->get('level')));
    return $app->redirect('/');
  } else {
    throw new LevelSelectionException("No level was selected");
  }
})->bind('level');;

$app->match('/', function(Request $request) use ($app, $app_name, $basic, $user, $answer_time) {
    if(!$app['session']->has('correct_answers')) {
      $app['session']->set('correct_answers', 0);
    }
    
    if(isset($user)) {
      if(!$app['session']->has('level')) {
        return $app['twig']->render('levels.html.twig', array("app_name" => $app_name, "appInfo" => new AppInfo(), 
            "basic" => $basic, "utils" => new Utils()));
      }
    
      $basic['points']= 0;
    
      $user_id = $user->getId();
      $question = $app['dao']->getNextQuestion($user_id);
      $alternatives = array();

      if($question) {
        $app['session']->set('right_user_id', $question[0]['user_id']);
        $daoAlternatives = $app['dao']->getAlternatives($user_id, $question[0]['user_id']);

        $randIndex = count($daoAlternatives) > 3 ? 3 : count($daoAlternatives);

        $rand_keys = array_rand($daoAlternatives, $randIndex);

        for($i = $randIndex-1; $i >= 0; $i--) {
          $alternatives[] = $daoAlternatives[$rand_keys[$i]];  
        }
        $alternatives[] = array('id'=>$question[0]['user_id'], 'name'=>$question[0]['name']);

        shuffle($alternatives);  
      }

      $basic['points']= $user->getPoints();
      
    }

    if ('POST' == $request->getMethod()) {
      if($app['session']->has('level')) {
        $level = $app['session']->get('level');
      }

      // if the user made the right choice
      echo "answer: ".$request->get('answer')." ".$app['session']->get('right_user_id');
      if(strcmp($request->get('answer'),$app['session']->get('right_user_id'))==0) {
        $app['session']->set('correct_answers', $app['session']->get('correct_answers')+1);

        $totalAvailableTime = $level->getTotalAvailableTime();
        $bonusFactor = $level->getBonusFactor();
        $timeToAnswer = ($answer_time - $app['session']->get('request_time'));
        $timeRemaining = $request->get('time_remaining');
        $serverTimeRemaining = $totalAvailableTime - $timeToAnswer;
        //If the player replied before the timeout and the server time is not too far off (no player cheating)
        if($timeRemaining != 0 && $serverTimeRemaining - $timeRemaining < 2) {
          $user->setPoints($user->getPoints() +  number_format((float)($bonusFactor * $timeRemaining / $totalAvailableTime), 2, '.', ''));
          $app['dao']->updateUserPoints($user);
          $basic['points']= $user->getPoints();
        }
      }
      $app['dao']->saveAnswer($user_id, $request->get('question'));
    }

    $app['session']->set('request_time', time());

    return $app['twig']->render('index.html.twig', 
        array("app_name" => $app_name, "appInfo" => new AppInfo(), 
            "basic" => $basic, "utils" => new Utils(), "question" => $question,
            "alternatives" => $alternatives, "availableTime" => isset($level) ? $level->getTotalAvailableTime() : 0));
})->bind('homepage');;


$app->error(function(\Exception $e, $code) use($app) {
  if ($app['debug']) {
      return;
  }
  return $app['twig']->render('error.html.twig', array('message' => $e->getMessage()));
});

return $app;