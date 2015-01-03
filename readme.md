# SRP-6a PHP Implementation

The SRP protocol has a number of desirable properties: it allows a user to authenticate
themselves to a server, it is resistant to dictionary attacks mounted by an eavesdropper,
and it does not require a trusted third party. It effectively conveys a zero-knowledge password
proof from the user to the server. In revision 6 of the protocol only one password can be guessed
per connection attempt. One of the interesting properties of the protocol is that even if one or
two of the cryptographic primitives it uses are attacked, it is still secure. The SRP protocol
has been revised several times, and is currently at revision 6a.

# Goal
To give people example of using SRP in their applications, so they became more secure.

# Usage Notes
This codebase provides JavaScript and PHP library code which perform an SRP proof of password. The JavaScript library code is in the folder `srp\Client\lib` and the PHP library code is in `srp\Server\lib`. 

The codebase also includes a demonstration application which uses jQuery and AJAX to register users into a [RedBean](http://redbeanphp.com/) database then authenticates them. RedBean which is used for demonstration purposes only and you may want to use another database (MySQL, PostresQL). 

If the authentication is successful the demo sets a PHP session variable `SRP_AUTHENTICATED` to the value `true`. There is also a PHP session variable `SRP_SESSION_KEY` which matches the JavaScript session key `sessionKey()` which is a strong shared secret key unique to the current authenticated session which could be used for further crypography. 
 

