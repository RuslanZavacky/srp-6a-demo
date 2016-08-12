<?php

namespace Rz\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Riimu\Kit\SecureRandom\SecureRandom;
use Rz\Service\Srp;
use Rz\Service\ControlledGenerator;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext
{

  private $email;
  private $password;
  private $salt;
  private $verifier;

  private $users = [];

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct()
  {
  }

  /**
   * @Given empty registration page
   */
  public function emptyRegistrationPage()
  {

  }

  /**
   * @When I submit registration form with email :email and password :password
   */
  public function iSubmitRegistrationFormWithEmailAndPassword($email, $password)
  {
    $this->email = $email;
    $this->password = $password;
  }

  /**
   * @Then I should receive salt and verifier
   */
  public function iShouldReceiveSaltAndVerifier()
  {

  }

  /**
   * @Then salt should be :salt and verifier should be :verifier
   */
  public function saltShouldBe($salt, $verifier)
  {
    $this->salt = $salt;
    $this->verifier = $verifier;
  }

  /**
   * @Then I try to login with salt and verifier
   */
  public function iTryToLoginWithSaltAndVerifier()
  {

  }

  /**
   * @Then login should be successful
   */
  public function loginShouldBeSuccessful()
  {

  }

  /**
   * @param TableNode $table
   *
   * @Given following users exists
   */
  public function followingUsersExists(TableNode $table)
  {
    foreach ($table as $row) {
      $this->users[] = [
        'email' => $row['email'],
        'salt' => $row['salt'],
        'verifier' => $row['verifier'],
        'B' => $row['B'],
      ];
    }
  }

  /**
   * @Then I request and validate issue challenge
   */
  public function iRequestAndValidateIssueChallenge()
  {
    foreach ($this->users as $user) {
      $srp = new Srp(new SecureRandom(new ControlledGenerator()));
      $srp->prepare($user['verifier'], $user['salt']);

      $challenge = $srp->issueChallenge();

      \PHPUnit_Framework_Assert::assertEquals($user['B'], $challenge['B']);
    }
  }
}
