# Errors

The filefabrik/Rfc9421 PHP composer package is working with PHP Exceptions.
Exceptions will be detailed thrown during your signing process (you are prepare a request or decode an incoming request
or signing a response)

All filefabrik/Rfc9421 Throwable's are abstracted from the class `AbstractRfc9421Exception`.
So you are able to cover all errors they will be thrown during the HTTP Message Signing and Verification Process.


## PrepareMethodsException

The PrepareMethods Class is responsible for holding/configuring Methods where component names will get there values from the HTTP Header
