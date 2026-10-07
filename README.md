# HTTP Message Signer and Verify (RFC 9421)

PHP RFC9421 HTTP Message Signing and Verify

## Features

✅ RFC 9421 Signing and Verification

✅ PHP-8.4|PHP-8.5

✅ CodeCoverage 100%

✅ Dependencies for live usage

    PSR-7 "psr/http-message"

✅ easy to use.

✅ integrate the signer and verifier into existing projects

✅ Use Guzzle or Laravel or other PSR-7 compatible libraries

✅ encapsulated processing

✅ change|set|extend your own signing algorithms via interface

✅ switch between php cryptography as your project need

## Release

The package is still on the developer instance while the final touches are being made before its first release. Would
you like to take a look at the code in advance? If so, please open a ticket.

## Getting started

### Install with composer

```shell
composer install filefabrik/rfc9421
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

#### Signing Tutorial

Let us have a look at the most relevant parameters when sending a Message (request) against another Server

First, you have to create your regular Request-Object. If you have already a Request-Object, you have nothing todo with
it.

What we have to do, we let your prepared Request running through die filefabrik/rfc9421 Signer

_typical request object_

```php
<?php 
use GuzzleHttp\Psr7\Request;

$bodyParams = ['recipient' => 'Alex', 'amount' => 900, 'currency' => 'EUR'];
$bodyJson   = json_encode($bodyParams, JSON_THROW_ON_ERROR);
$request    = new Request(
    method : 'POST',
    uri    : 'https://target-host.com',
    headers: ['x-origin-host' => 'target-host.com'],
    body   : $bodyJson,
);

?>
```

Looks quite a normal request. Now, let us have a look for the Signer-Configuration.

**Full example of request signer**

```php
<?php 
/**
 * Prepare signingConfiguration
 */
$currentDateTime = new DateTime()->getTimestamp();

$signingConfiguration = new SigningConfiguration()
    ->signHeader('@method')
    ->signHeader('x-Origins-host')
    ->withSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
    ->withCreated($currentDateTime)
    ->withExpires($currentDateTime + 7200)
    ->withTag('mastodon')
    ->withKeyid('http://127.0.0.1:8001/actor#main-key')
    ->withNonce('nonce-now')
    ->setContentDigestHashAlgo('sha512')
    // content digest signer key
    ->withSignatureKeyName('sig=')
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

## Explained signing configuration

```php
<?php 
use Filefabrik\Rfc9421\SigningConfiguration;

$configuration   = new SigningConfiguration();
?>
```

### Signing Headers

You can set more header entries from your request as "signed" so the receiver can be sure, that this header-entries also
valid and not tempered/fixed/hacked from somewhere

```php
<?php 

$signingConfiguration->signHeader('@method')
    ->signHeader('x-Origin-host');
?>
```
or:
```php
<?php 

$signingConfiguration->signHeader('@method','x-Origin-host');
?>
```

### Signing Algorithm (required)

The algorithm must be implemented at your server and at the target-server.

```php
<?php 
$signingConfiguration->withSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512);
?>
```

it can be a string or an enum `SigningEnum::rsa_v_1_5_sha_512`

### ContentDigest Hash default sha-256

One important aspect is hashing the "body" of the POST Message.
The hashed "content-digest" will be part in the signed message. So the receiver know that the "body" of the Message was
not modified.

```php
<?php 
use Filefabrik\Rfc9421\Support\DigestAlgorithmsEnum;

$signingConfiguration->setContentDigestHashAlgo(DigestAlgorithmsEnum::sha512);
?>
```
other forms of writings are also valid:
```php
<?php 
 
$signingConfiguration->setContentDigestHashAlgo('sha256');
// or
$signingConfiguration->setContentDigestHashAlgo('sha-256');
// or 
$signingConfiguration->setContentDigestHashAlgo('sha512');
// or
$signingConfiguration->setContentDigestHashAlgo('sha-512');
?>
```



### Private Key Pem (required)

To create a signed message, you need your private key. With your public key pem the receiver can check the signed
message that can only be come from your server.

```cert
-----BEGIN PRIVATE KEY-----
MIHsAgEAMBQGByqGSM49AgEGCSskAwMCCAEBDgSB0DCBzQIBAQRAUuppIGbSl5mW
cU1MuO9R3DR7HUNK77BbRIjC5UxRdN7uWRoUnBEVG9SkXmxggIR2b91JRLtzHuwb
corEJiM77KGBhQOBggAEmn3TVHPwtiGXHm8ss3FXA9J4Bp+emMUaLO3Fn9RZ4eYk
/ZLPoFlanf9go/1nrIfWFYLHmIQJGkojkdcxhKQ3oReSW+4oYUgBTZe9QXJmntO0
qmtY4EdmScMucHmOtfo4SovgL47u0jReWH/X8DVYCQW+BtU/LEXIVIoMBeXkxac=
-----END PRIVATE KEY-----
```

```php
<?php 
$signingConfiguration->setPrivatePem(file_get_contents('the-private-key.pem'));
?>
```

### signingBody (default=true, optional but proposed!)

The reason of the RFC9421 is to make sure, some information's of the request are not modified after signing.
I think a signedBody is mandatory for Post Requests.
Per default the body will be automatically signed.
That makes sure, the body was never tempered.

But if you **don't want sign the body**, set it to **false**

```php
<?php 
$signingConfiguration->mustSignBody(false);
?>
```

### Nonce (optional)

when you are the sender you can set each request with your own "nonce". The receiver can check that he is only one time
processing your request.

```php
<?php 
$signingConfiguration->withNonce(rand(1,100).'something unique from your server');
?>
```

### create (optional)

Timestamp UTC! when you are sending the message
Note: receiver can ignore it

```php
<?php 
$signingConfiguration->withCreated( new DateTime()->getTimestamp());
?>
```

### expires (optional)

Tell the receiver the timestamp when the request is expired.
Note: receiver can ignore it

```php
<?php 
$signingConfiguration->withExpires( new DateTime()->getTimestamp() + 3600);
?>
```

### tag (optional)

If your RFC9421 Message has a special format and the target-server need this information.
Note: receiver can ignore it

```php
<?php 
$signingConfiguration->withTag('fediverse');
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

PSR-7: HTTP message interfaces
https://www.php-fig.org/psr/psr-7/

Under docs the CSV's for

* component-parameters
* signature-algorithms
* signature-metadata-parameters

https://www.iana.org/assignments/http-message-signature

Testing http signatures: https://httpsig.org/

To create Certs have a look into ./tests/generators/ there are some generators you can modify as you need if you need

or via a tool: https://toolv.com/en/app/rsa-key-generator  or simply with local terminal

## todos

* multiple signatures
* prepareComponents
    * 2.2.5. Request Target
    * 2.2.7. Query
    * 2.4. Signing Request Components in a Response Message
* check https://github.com/bakame-php/http-structured-fields
