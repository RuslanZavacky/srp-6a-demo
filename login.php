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
    $_SESSION['SRP_AUTHENTICATED'] = false;
    $_SESSION['SRP_SESSION_KEY'] = $srp->getSesionKey();
    $_SESSION['SRP_USER_ID'] = $user->email;
    
    $result = array('challengeResponse' => $challenge, 'debug' => array(
      'session' => $_SESSION,
      'user' => $user
    ));
  }
} elseif(!empty($_POST['respondToChallenge']['M'])) {
  if ($_POST['respondToChallenge']['M'] === $_SESSION['M']) {
    // put the key in the session which shows the the user is authenticated
    $_SESSION['SRP_AUTHENTICATED'] = true;
    
    //$tmp = "".$_SESSION['SRP_AUTHENTICATED']." ".$_SESSION['SRP_USER_ID']." ".$_SESSION['SRP_SESSION_KEY'];
    
    // return the server proof (demonstrates server knew the actual registered verifier and so a valid shared key 'K')
    $result = array(
        'verifyResponse' => array(
            'HAMK' => $_SESSION['HAMK']
        )
    );

    // free the proofs as no longer needed
    unset($_SESSION['M']);
    unset($_SESSION['HAMK']);
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
