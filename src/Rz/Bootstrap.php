<?php

namespace Rz;

use RedBeanPHP\R;
use Rz\Controller\AuthController;

class Bootstrap
{
  public function __construct()
  {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    session_start();

    R::setup('sqlite:srp_db.txt');
  }

  public function route($path = '/')
  {
    $controller = new AuthController();


    switch ($path) {
      case '/login':
        $out = $controller->loginAction();
        break;

      default:
      case '/':
        $out = $controller->indexAction();
        break;
    }

    echo $out;
  }
}

