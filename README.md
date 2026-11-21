# HTTP Message Signer and Verify (RFC 9421)

PHP RFC9421 HTTP Message Signing and Verify

## Features

✅ RFC 9421 Signing and Verification for Requests and Responses

✅ PHP-8.4|PHP-8.5

✅ CodeCoverage 100% over 460< Tests and 830< Assertions

✅ easy to use.

✅ integrate the signer and verifier into existing projects

✅ Use Guzzle or Laravel or other PSR-7 compatible libraries

✅ encapsulated processing

✅ change|set|extend your own signing algorithms via interface

✅ switch between php cryptography as your project need

✅ Dependencies for live usage

    PSR-7 "psr/http-message"
    RFC9561 and RFC8941 bakame/http-structured-fields

## Release

The package is still on the developer instance while the final touches are being made before its first release. Would
you like to take a look at the code in advance? If so, please write a ticket.

## Diagnostic Tools

Fresh implementation of message singing and verifying can be hard.
So we have some Tools to check step by step the signing/verifiying process

* [diagnostics.md](docs/diagnostics.md)

## Need professional Support?

Es kann sehr aufwendig sein einen Message-Signing und Verification Prozess ein neues oder bestehendes System zu
implementieren.
Möchten Sie die Lernkurve bei der Implementierung mit dem RFC9421 Standard auf einem kostengünstigen Niveau halten?
Wünschen Sie eine Prüfung Ihres implementierten Prozesses?
Wir Ihnen unsere Unterstützung an. Zögern Sie nicht uns zu kontaktieren.

## Documentation Details

PHP Error Messages (Throwable's)

* [errors.md](docs/errors.md)

Contribute/Listing for other HTTP Signing Interfaces

* [Contribute.md](docs/Contribute.md)

## Getting started

### Install with composer

```shell
composer install filefabrik/rfc9421
```

## Examples

* a Full-Example from console without spinning an extra webserver
* a signing Example

### Full HTTP Signing and Verify Example over two Servers

To help you to understand and test all the processes in RFC 9421, we have provided a complete example.

All processes

* Alice-Server
    * Creating a request
    * Signing a request
    * Sending the signed request
* Bob's Server
    * Receiving the signed request
    * Verifying the signed request
    * Processing the verified request
    * Signing the response for Alice
    * Response the signed response
* Alice Server
    * Verifying the signed response

are provided as a transparent and easy-to-follow example.

Feel free to experiment with the ready-to-use example to familiarize yourself with all aspects of the RFC 9421 message
signing and verification process.

If you have any requests or ideas, please create a ticket.

see in `/examples/full-flow.php`

```shell
php ./examples/full-flow.php
```

### Example

For getting directly results in your code, have a look into `./examples/signer.php` there is a full example of signing
and verifying with Rfc9421.

### Signing Request Example

The signing process should be easy for you to handle without deeper knowledge of the internals signing and processings.

* Create a regular Request (Guzzle or something else) via an PSR-7 RequestInterface
* Configure the Signer
* Get back the request object from the signing process and send the request

That's all

#### Detailed Signing Request  Tutorial

Let us have a look at the most relevant parameters when sending a Message (request) against another Server

First, you have to create your regular Request-Object. If you have already a Request-Object, you have nothing todo with
it.

What we have to do, we let your prepared Request running through die filefabrik/rfc9421 Signer

_typical request object_

```php
<?php 
use GuzzleHttp\Psr7\Request;

$request    = new Request(
    method : 'POST',
    uri    : 'https://target-host.com',
    headers: ['x-origin-host' => 'target-host.com'],
    body   : '{"recipient":"Alex","amount":900,"currency":"EUR"}',
);

?>
```

Looks quite a normal request. Now, let us have a look for the Signer-Configuration.

**Full example of request signer**

```php
<?php
use Filefabrik\Rfc9421\SigningComponents;
use Filefabrik\Rfc9421\SigningConfiguration;

/**
* Components you want to sign
 */
$signingComponents= new SigningComponents()
    ->sign('@method')
    ->sign('x-Origins-host');
 

$currentDateTime = new DateTime()->getTimestamp();
/**
 * Prepare signingConfiguration
 */
$signingConfiguration = new SigningConfiguration($signingComponents)
    ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
    ->setCreated($currentDateTime)
    ->setExpires($currentDateTime + 7200)
    ->setTag('mastodon')
    ->setKeyid('http://127.0.0.1:8001/actor#main-key')
    ->withNonce('nonce-now')
    ->withContentDigest('sha512')
    // content digest signer key
    ->setSignatureKeyName('sig=')
    // for signing needs private
    ->setPrivatePem('your-private.pem')
;
?>
```

**Simply sign the request with the configured parameters**

```php
<?php 
$signer        = new Signer(message: $request, signingConfiguration: $signingConfiguration);
$signedRequest = $signer->sign();

/**
* Send the signed request
*/
$response = new Client()->send($signedRequest);
?>
```

_That's All_

#### Verify incoming Request

* Take the Request (PSR-7 RequestInterface) Object
* prepare the request in the Verifier
* Getting the Public Key from the Sender and configure some parts if need
* Verify

```php
<?php 
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\VerifyRequestPrepare;
use Filefabrik\Rfc9421\VerifyRequest;

$verifierConfiguration = new SigningConfiguration();
$verifyPrepareInstance = new VerifyRequestPrepare($signedRequest, $verifierConfiguration);

/**
 * Prepare the incoming Request for verifying
 * after $verifyPrepareInstance->prepareVerification(); you are able to configure/check/load(i.e. the public pem from the sender) and then you handle the real verify process
 * important, throws if something wrong
 */
$verifyPrepareInstance->prepareVerification();

// if there is no exception thrown you can go further.
// For mastodon, you have to download the user public pem if not already done
// download or get from your local cache the users pem
$downloadedUsersPem = '--- pem ---';
$verifierConfiguration->setPublicPem(publicPem: $downloadedUsersPem);

$verifier = new VerifyRequest($verifyPrepareInstance);
/**
* Throws if something went wrong during the verifying
 */
$verifiedBool = $verifier->verifying();

if(!$verifiedBool){
    echo 'Signature was not verified.'
    return;
}

// If you need a signed body, check the body was in the signer process from the requester.
// At this point, if the requester singend the body, the body is automatically verified  via the $verifier->verifying();
if ($verifier->hasSignedBody()) {
    echo 'Cool, body verified'."\n";
    $body = (string)$verifier->getMessage()
                             ->getBody()
    ;
} else {
    echo 'body was not part of verifying process'."\n";
}

?>
```

## Explained signing configuration

For Request and Response Signing use the `SigningConfiguration`. 
```php
<?php 
use Filefabrik\Rfc9421\SigningConfiguration;

$signingConfiguration   = new SigningConfiguration();
?>
```

### Signing Components

You can set more header entries from your request/response as "signed" so the receiver can be sure, that this header-entries also valid and not tempered/fixed/hacked from somewhere.

```php
<?php 
use Filefabrik\Rfc9421\SigningComponents;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum


$signingComponents = new SigningComponents()
     ->sign('@method') 
     ->sign('x-Origin-host')
     ->sign(DerivedComponentEnum::requestTarget)
     // Signing "$headerKey";bs
     // Signing "$headerKey";req;bs
     ->signWithBS('example-dict')
     // Signing "$headerKey";sf
     // Signing "$headerKey";req;sf
     ->signWithSF('example-dict')
     // Signing "$headerKey";key="$keyValue"
     // Signing "$headerKey";req;key="$keyValue"
     //->signHeader('"example-dict";key="d"') or better way like:
     ->signWithKey('example-dict', 'd')
     // Signing "$headerKey";$fieldName="$nameValue"
     // Signing "$headerKey";req;$fieldName="$nameValue"
     //->signHeader('"@query-param";$fieldName="baz"') or better way like:
     ->signWithName(DerivedComponentEnum::queryParam, 'baz')
     //..other signings
$signingConfiguration = new SigningConfiguration();   
// push the signingComponents into the SigningConfiguration
$signingConfiguration->setSigningComponents($signingComponents)
        // ..other configs
;

?>
```

or:

```php
<?php 
use Filefabrik\Rfc9421\SigningComponents;
/** @var \Filefabrik\Rfc9421\SigningConfiguration $signingConfiguration */
$signingComponents= new SigningComponents()->signHeaders(['@method','x-Origin-host']);

$signingConfiguration = new SigningConfiguration();   
// push the signingComponents into the SigningConfiguration
$signingConfiguration->setSigningComponents($signingComponents)
?>
```

Note:

* Use for the Derived Components the Derived Component Enum `DerivedComponentEnum`
* if you know your exactly component, make precise and specify the component with:
  * `->sign('x-Origin-host')`
  * `->signWithBS('example-dict')`
  * `->signWithSF('example-dict')`
  * `->signWithKey('example-dict', 'd')` 
  * `->signWithName(DerivedComponentEnum::queryParam, 'baz')`

instead of the `->setHeader()` or `->setHeaders()` method

### Signing Algorithm (required)

The algorithm must be implemented at your server and at the target-server.

```php
<?php 
/** @var \Filefabrik\Rfc9421\SigningConfiguration $signingConfiguration */
$signingConfiguration->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512);
?>
```

it can be a string or an enum `SigningEnum::rsa_v_1_5_sha_512`

### The magic "content-digest"

One important aspect is hashing the "body" of the POST Message.
The hashed "content-digest" will be part in the signed message. So the receiver know that the "body" of the Message was
not modified.

The reason of the RFC9421 is to make sure, some information's of the request are not modified after signing.
I think a signedBody is mandatory for Post Requests.
Per default the body will NOT be automatically signed.
To make signing the body with automatically insert the header "content-digest" use the following configuration:

```php
<?php 
use Filefabrik\Rfc9421\Enums\DigestAlgorithmsEnum;
/** @var \Filefabrik\Rfc9421\SigningConfiguration $signingConfiguration */
$signingConfiguration->withContentDigest(DigestAlgorithmsEnum::sha512);
?>
```

other forms of writings are also valid:

```php
<?php 

/** @var \Filefabrik\Rfc9421\SigningConfiguration $signingConfiguration */
$signingConfiguration->withContentDigest('sha256');
// or
$signingConfiguration->withContentDigest('sha-256');
// or 
$signingConfiguration->withContentDigest('sha512');
// or
$signingConfiguration->withContentDigest('sha-512');
?>
```

### Private Key Pem (required)

To create a signed message, you need your private key. With your public key pem the receiver can check the signed
message that can only be come from your server.

Example: 
```php
<?php 
/** @var \Filefabrik\Rfc9421\SigningConfiguration $signingConfiguration */
$signingConfiguration->setPrivatePem(file_get_contents('the-private-key.pem'));
?>
```

### Metadata

#### Nonce (optional)

when you are the sender you can set each request with your own "nonce". The receiver can check that he is only one time
processing your request.

```php
<?php 
$signingConfiguration->withNonce(rand(1,100).'something unique from your server');
?>
```

#### create (optional)

Timestamp UTC! when you are sending the message
Note: receiver can ignore it

```php
<?php 
$signingConfiguration->setCreated( new DateTime()->getTimestamp());
?>
```

#### expires (optional)

Tell the receiver the timestamp when the request is expired.
Note: receiver can ignore it

```php
<?php 
$signingConfiguration->setExpires( new DateTime()->getTimestamp() + 3600);
?>
```

#### tag (optional)

If your RFC9421 Message has a special format and the target-server need this information.
Note: receiver can ignore it

```php
<?php 
$signingConfiguration->setTag('fediverse');
?>
```

## Customization

You can customize the package for your needs

[custom-derived-component-generation.md](docs/custom-derived-component-generation.md)

@todo how to configure/modify crypto-libraries for the package

## Signing/Verify Algorithms

Per default the following Algorithms are implemented as RFC9421 describes

* rsa-pss-sha512
* rsa-v1_5-sha256
* rsa-v1-5-sha384
* rsa-v1-5-sha512
* hmac-sha256
* hmac-sha384
* hmac-sha512
* ecdsa-p256-sha256
* ecdsa-p384-sha384
* ed25519

### Need other Signing Algorithms? No Problem!

If you need more algorithms it is easy to implement new algorithms without changing the codebase of this package

todo: show example in tests directory how to implement

## PHP Signing libraries

This RFC9421-Library is shipped without a Cryptography library. So you are able to use an in your project existing
signing
library. For fresh Projects, pick a singing library as you need.

To use a preconfigured and tested signing and verifying Configuration please use require_once command somewhere before
signing oar verifying something.

### OpenSSL (fully configured)

```php
<?php 
/**
 * use openssl (native php extension) which so preconfigured
 * if you need another library oar need some modifications, build your own
 */
require_once dirname(__DIR__).'/src/register-openssl-signing-verify.php';
?>
```

### phpseclib4 (not fully configured)

https://phpseclib.com/docs/publickeys/overview

```php
<?php 
/**
 * use phpseclib4 library which is preconfigured
 * if you need another library oar need some modifications, build your own
 */
require_once dirname(__DIR__).'/src/register-phpseclib4-signing-verify.php';
?>
```

### EasyECC (under construction)

## Certificate basics and references

### RSA

* https://mojoauth.com/security-guides/generate-keypair-using-rsa-with-shell

### Ed25519

* https://mojoauth.com/security-guides/generate-keypair-using-ed25519-with-php#how-do-i-sign-and-verify-a-message
* https://mojoauth.com/security-guides/generate-keypair-using-ed25519-with-shell#how-to-generate-an-ed25519-key-with-openssl

### ECDSA

* https://mojoauth.com/security-guides/generate-keypair-using-ecdsa-with-php#how-to-sign-and-verify-data-with-the-ecdsa-keys-in-php
* https://mojoauth.com/security-guides/generate-keypair-using-ecdsa-with-shell

### HMAC

@see  https://mojoauth.com/security-guides/

## Use-Cases

mastodon https://docs.joinmastodon.org/spec/security/ uses 3.3.2. RSASSA-PKCS1-v1_5 Using SHA-256

## Docs and other References

RFC9421 https://www.rfc-editor.org/info/rfc9421

Structured Field Values for HTTP
RFC9651 https://www.rfc-editor.org/rfc/rfc9651.html

PSR-7: HTTP message interfaces
https://www.php-fig.org/psr/psr-7/

Under docs the CSV's for

* component-parameters
* signature-algorithms
* signature-metadata-parameters

https://www.iana.org/assignments/http-message-signature

Testing http signatures: https://httpsig.org/ // will not work. js errors in website

To create Certs have a look into ./tests/generators/ there are some generators you can modify as you need if you need

or via a tool: https://toolv.com/en/app/rsa-key-generator  or simply with local terminal

## todos

* Signing response example after receive a request
    * documentation for signing the response without processing `content-length` during generation and other RFC header
      variables they will be injected into request|response after Filefabrik/Rfc9421 signing
* multiple signatures
* prepareComponents
    * 2.2.5. Request Target
    * 2.2.7. Query
    * 2.4. Signing Request Components in a Response Message
* check https://github.com/bakame-php/http-structured-fields
    * [structured-fields.md](docs/structured-fields.md)
* for symfony composer require symfony/psr-http-message-bridge
    * `"symfony/http-foundation": "^v8.1.8",
      "symfony/psr-http-message-bridge": "^v8.1.0",
      "nyholm/psr7": "^1.8"`
* todo diagnose/testmethods to implement faster a new requester/vendor to check there stuff and also explain detailed
  what happens during your or there processing
* doc structured field types and mappers need for "Key" "Sf"
* doc "./Methods" default handling and how to change
* query param and key handler is really buggy.  
  can also not work at `macgirvin/http-message-signer`
* todo more strict header enums for singing components with an extra validator "I expect the following signed parameters"
```php
<?php 
use Filefabrik\Rfc9421\Methods\MessageQueryParams;MessageQueryParams::handle()
?>
```
