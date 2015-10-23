@users
Feature: Users
  In order to manage users
  As a consumer
  I need to be able to access and manage them

  Scenario: Successfully create a User
    #Given ...
    When I send GET request to "/"
    Then response status code should be 200
    And the database should contain a user with the following data
      | username | ppaulis                         |
      | email    | pascal.paulis@continuousphp.com |