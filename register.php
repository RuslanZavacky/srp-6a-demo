<?php
/**
 * @author      ruslan.zavackiy
 */

require 'lib/require.php';

if (!empty($_POST['registerBtn'])) {
  $user = R::findOne('user', 'email = :email', array(
    ':email' => $_POST['email']
  ));

  if (empty($user)) {
    $user = R::dispense('user');
    $user->email = $_POST['email'];
    $user->password_salt = $_POST['password_salt'];
    $user->password_verifier = $_POST['password_verifier'];

    $id = R::store($user);

    $result = array('user-id' => $id);
  } else {
    $result = array('error' => 'User with email <b>' . htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') . '</b> already exists');
  }

  echo json_encode($result);

  exit();
}

include $root . '/templates/register.phtml';