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