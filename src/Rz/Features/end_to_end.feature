Feature: SRP-6a End-To-End Implementation Tests
  In order to validate that frontend and backedn parts are working correctly
  As a customer
  I need to be able to register & login into the system

  Scenario: Account Registration Should Provide With Salt and Verifier
    Given empty registration page
    When I submit registration form with email "user@email.com" and password "secure-password"
    Then I should receive salt and verifier
    And salt should be "123" and verifier should be "456"
    Then I try to login with salt and verifier
    And login should be successful