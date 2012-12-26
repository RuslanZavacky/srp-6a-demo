<?php
/**
 * @author      ruslan.zavackiy
 */

require 'lib/require.php';

$result = array();

if (!empty($_POST['challenge'])) {
  $user = R::findOne('user', 'email = :email', array(
    ':email' => $_POST['email']
  ));

  if (empty($user)) {
    $result = array('error' => 'No user with such email');
  } else {
    $srp = new Srp($user->password_verifier, $user->password_salt);

    $challenge = $srp->issueChallenge($_POST['challenge']['A']);

    $_SESSION['M'] = $srp->getM();
    $_SESSION['HAMK'] = $srp->getHAMK();

    $result = array('challengeResponse' => $challenge, 'debug' => array(
      'session' => $_SESSION,
      'user' => $user
    ));
  }
} elseif(!empty($_POST['respondToChallenge']['M'])) {
  if ($_POST['respondToChallenge']['M'] === $_SESSION['M']) {
    $result = array(
      'verifyResponse' => array(
        'HAMK' => $_SESSION['HAMK']
      )
    );
  } else {
    $result = array(
      'error' => 'Authentication failed',
      'debug' => array(
        'post' => $_POST,
        'session' => $_SESSION
      )
    );
  }
}

echo json_encode($result);

exit();