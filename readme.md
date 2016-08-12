# SRP-6a PHP Implementation

The SRP protocol has a number of desirable properties: it allows a user to authenticate
themselves to a server, it is resistant to dictionary attacks mounted by an eavesdropper,
and it does not require a trusted third party. It effectively conveys a zero-knowledge password
proof from the user to the server. In revision 6 of the protocol only one password can be guessed
per connection attempt. One of the interesting properties of the protocol is that even if one or
two of the cryptographic primitives it uses are attacked, it is still secure. The SRP protocol
has been revised several times, and is currently at revision 6a. [Wikipedia](https://en.wikipedia.org/wiki/Secure_Remote_Password_protocol)

# Setup

Requirements:
  * bower
  * composer
  * PHP >= 5.6

`composer install && bower install`

# SRP Protocol Design
[Protocol Design](http://srp.stanford.edu/design.html)

# Goal
To give people example of using SRP in their applications.

# Usage Notes
This codebase provides JavaScript and PHP library code which perform an SRP proof-of-password. 
The JavaScript library code is in the folder `public/assets/js/app` and the PHP library code is in `src/`. 

The codebase includes a demonstration application which uses jQuery AJAX and [RedBean](http://redbeanphp.com/) 
to register users into a SQLite database then authenticates them. SQLite attempts to write into the `public/` directory 
of the website but the path can be edited in `src/Bootstrap.php`. RedBean and SQLite are used for demonstration 
purposes only and are not needed to use the core SRP library code. 

If the authentication is successful then a PHP session variable `SRP_AUTHENTICATED` is set to `true`. 
This indicates that the session variables `SRP_USER_ID` and `SRP_SESSION_KEY` have been authenticated. 
The session key variable matches the JavaScript session key `sessionKey()` and as a strong shared secret key 
unique to the current authenticated session which could be used for further crypography.
