<?php
 /**
 * @author      ruslan.zavackiy
 * @since       X.X
 * @version     $Id$
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$root = dirname(dirname(__FILE__));

session_start();

require $root . '/lib/rb.php';
require $root . '/srp/Server/BigInteger.php';
require $root . '/srp/Server/Srp.php';

R::setup('sqlite:srp_db.txt');