<?php

namespace WhoSaidThat;

/**
 * This class provides static methods that return pieces of data specific to
 * your app
 */
class AppInfo {

  /*****************************************************************************
   *
   * These functions provide the unique identifiers that your app users.  These
   * have been pre-populated for you, but you may need to change them at some
   * point.  They are currently being stored in 'Environment Variables'.  To
   * learn more about these, visit
   *   'http://php.net/manual/en/function.getenv.php'
   *
   ****************************************************************************/

  /**
   * @return the appID for this app
   */
  public static function appID() {
    return getenv('FACEBOOK_APP_ID');
  }

  /**
   * @return the appSecret for this app
   */
  public static function appSecret() {
    return getenv('FACEBOOK_SECRET');
  }

  /**
   * @return the url
   */
  public static function getUrl($path = '/') {
    if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
      || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
    ) {
      $protocol = 'https://';
    }
    else {
      $protocol = 'http://';
    }

    return $protocol . $_SERVER['HTTP_HOST'] . $path;
  }
  
  public static function getHost() {
      return $_SERVER['HTTP_HOST'];
  }

  public static function getDatabaseUrl() {
    return parse_url(getEnv('DATABASE_URL'));
  }

  public static function getDbHost() {
    extract(AppInfo::getDatabaseUrl());
    return $host;
  }

  public static function getDbName() {
    extract(AppInfo::getDatabaseUrl());
    return substr($path, 1);
  }

  public static function getDbUser() {
    extract(AppInfo::getDatabaseUrl());
    return $user;
  }

  public static function getDbPass() {
    extract(AppInfo::getDatabaseUrl());
    return $pass;
  }
  

}
