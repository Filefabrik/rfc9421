<?php

declare(strict_types=1);

use Filefabrik\Hsap\Cryptography\SignatureRegistry;
use Filefabrik\Hsap\Cryptography\SigningEnum;
use Filefabrik\Hsap\Cryptography\VerifyRegistry;
use phpseclib4\Crypt\RSA;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';

/**
 * Sender-Side
 * https://toolv.com/en/app/rsa-key-generator
 */

/**
 * signer config
 */
$signingRsa256 = function(string $signingDatas, $privateKeyPem) {
    return RSA::loadPrivateKey($privateKeyPem)
              ->withHash('sha256')
              ->sign($signingDatas)
    ;
};
$signingRsa384 = function(string $signingDatas, $privateKeyPem) {
    return RSA::loadPrivateKey($privateKeyPem)
              ->withHash('sha384')
              ->sign($signingDatas)
    ;
};
$signingRsa512 = function(string $signingDatas, $privateKeyPem) {
    return RSA::loadPrivateKey($privateKeyPem)
              ->withHash('sha512')
              ->sign($signingDatas)
    ;
};

$signingMethodsRsaPhpSeclib4 = [
    SigningEnum::rsa_v_1_5_sha_256->name => $signingRsa256,
    SigningEnum::rsa_v_1_5_sha_384->name => $signingRsa384,
    SigningEnum::rsa_v_1_5_sha_512->name => $signingRsa512,
];

SignatureRegistry::registerSigningMethods(signingMethods: $signingMethodsRsaPhpSeclib4);

/**
 * Verifier config
 */
$verifyRsa256 = function(string $signingDatas, $signature, $publicKeyPem) {
    return RSA::loadPublicKey($publicKeyPem)
              ->withHash('sha256')
              ->verify($signingDatas, $signature)
    ;
};
$verifyRsa384 = function(string $signingDatas, $signature, $publicKeyPem) {
    return RSA::loadPublicKey($publicKeyPem)
              ->withHash('sha384')
              ->verify($signingDatas, $signature)
    ;
};
$verifyRsa512 = function(string $signingDatas, $signature, $publicKeyPem) {
    return RSA::loadPublicKey($publicKeyPem)
              ->withHash('sha512')
              ->verify($signingDatas, $signature)
    ;
};

$verifierMethodsRsaPhpSeclib4 = [
    SigningEnum::rsa_v_1_5_sha_256->name => $verifyRsa256,
    SigningEnum::rsa_v_1_5_sha_384->name => $verifyRsa384,
    SigningEnum::rsa_v_1_5_sha_512->name => $verifyRsa512,
];

VerifyRegistry::registerVerifyingMethods(verifyingMethods: $verifierMethodsRsaPhpSeclib4);
