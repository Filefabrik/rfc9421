# Errors

The filefabrik/Rfc9421 PHP composer package is working with PHP Exceptions.
Exceptions will be detailed thrown during your signing process (you are prepare a request or decode an incoming request
or signing a response)

All filefabrik/Rfc9421 Throwable's are abstracted from the class `AbstractRfc9421Exception`.
So you are able to cover all errors they will be thrown during the HTTP Message Signing and Verification Process.
If there is an standard php-exception in the package, please let me know. Iam expecting that all errors inside this
package are covered by a `AbstractRfc9421Exception` Exception Class.

## PrepareMethodsException

The PrepareMethods Class is responsible for holding/configuring Methods where component names will get there values from
the HTTP Header

## QueryParamsException

The QueryParamsException are used if there are something went wrong while checking the QueryParams in combination with
the Derived`@query-params`

## ProcessingException

If you want a public object that was not created before.

## EmptyException

If missing certificates for signing or validation
Note: This error does NOT tell you that the certificate is valid

* `privatePemIsEmpty`
* `publicPemIsEmpty`

## StructuredFieldException

* `unknownStructuredFieldType` An structured field was proceeded and returns null. Null as return is not allowed during
  the handling a structured field because that means with the value can not be proceeded
