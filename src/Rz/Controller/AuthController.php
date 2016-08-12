<?php

namespace Rz\Controller;

use RedBeanPHP;
use RedBeanPHP\R;
use Riimu\Kit\SecureRandom\Generator\OpenSSL;
use Riimu\Kit\SecureRandom\SecureRandom;
use Rz\Service\Srp;

class AuthController
{
  public function indexAction()
  {
    if (!empty($_POST['registerBtn'])) {
      $user = R::findOne('user', 'email = :email', array(
        ':email' => $_POST['email']
      ));

      if (empty($user)) {
        /** @var RedBeanPHP\SimpleModel $user */
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

    ob_start();

    include __DIR__ . '/../Template/index.phtml';

    return ob_get_clean();
  }

  public function loginAction()
  {
    $result = array();

    if (!empty($_POST['challenge'])) {
      $user = R::findOne('user', 'email = :email', array(
        ':email' => $_POST['email']
      ));

      if (empty($user)) {
        $result = array('error' => 'No user with such email');
      } else {
        $srp = new Srp(new SecureRandom(new OpenSSL()));
        $srp->prepare($user->password_verifier, $user->password_salt);

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

    return json_encode($result);
  }
}