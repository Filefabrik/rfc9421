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
$signingRsaPSSSha512 = function(string $signingDatas, $privateKeyPem) {
    return RSA::loadPrivateKey($privateKeyPem)
              ->withHash('sha512')
              ->withPadding(RSA::SIGNATURE_PSS)
              ->sign($signingDatas)
    ;
};
$signingRsaPKCS1256  = function(string $signingDatas, $privateKeyPem) {
    return RSA::loadPrivateKey($privateKeyPem)
              ->withHash('sha256')
              ->withPadding(RSA::SIGNATURE_PKCS1)
              ->sign($signingDatas)
    ;
};
$signingRsaPKCS1384  = function(string $signingDatas, $privateKeyPem) {
    return RSA::loadPrivateKey($privateKeyPem)
              ->withHash('sha384')
              ->withPadding(RSA::SIGNATURE_PKCS1)
              ->sign($signingDatas)
    ;
};
$signingRsaPKCS1512  = function(string $signingDatas, $privateKeyPem) {
    return RSA::loadPrivateKey($privateKeyPem)
              ->withHash('sha512')
              ->withPadding(RSA::SIGNATURE_PKCS1)
              ->sign($signingDatas)
    ;
};

$signingMethodsRsaPhpSeclib4 = [
    SigningEnum::rsa_pss_sha512->name    => $signingRsaPSSSha512,
    SigningEnum::rsa_v_1_5_sha_256->name => $signingRsaPKCS1256,
    SigningEnum::rsa_v_1_5_sha_384->name => $signingRsaPKCS1384,
    SigningEnum::rsa_v_1_5_sha_512->name => $signingRsaPKCS1512,
];

SignatureRegistry::registerSigningMethods(signingMethods: $signingMethodsRsaPhpSeclib4);

/**
 * Verifier config
 */
$verifyRsaPSSSha512 = function(string $signingDatas, $signature, $publicKeyPem) {
    return RSA::loadPublicKey($publicKeyPem)
              ->withHash('sha512')
              ->withPadding(RSA::SIGNATURE_PSS)
              ->verify($signingDatas, $signature)
    ;
};

$verifyRsaPKCS1256 = function(string $signingDatas, $signature, $publicKeyPem) {
    return RSA::loadPublicKey($publicKeyPem)
              ->withHash('sha256')
              ->withPadding(RSA::SIGNATURE_PKCS1)
              ->verify($signingDatas, $signature)
    ;
};
$verifyRsaPKCS1384 = function(string $signingDatas, $signature, $publicKeyPem) {
    return RSA::loadPublicKey($publicKeyPem)
              ->withHash('sha384')
              ->withPadding(RSA::SIGNATURE_PKCS1)
              ->verify($signingDatas, $signature)
    ;
};
$verifyRsaPKCS1512 = function(string $signingDatas, $signature, $publicKeyPem) {
    return RSA::loadPublicKey($publicKeyPem)
              ->withHash('sha512')
              ->withPadding(RSA::SIGNATURE_PKCS1)
              ->verify($signingDatas, $signature)
    ;
};

$verifierMethodsRsaPhpSeclib4 = [
    SigningEnum::rsa_pss_sha512->name    => $verifyRsaPSSSha512,
    SigningEnum::rsa_v_1_5_sha_256->name => $verifyRsaPKCS1256,
    SigningEnum::rsa_v_1_5_sha_384->name => $verifyRsaPKCS1384,
    SigningEnum::rsa_v_1_5_sha_512->name => $verifyRsaPKCS1512,
];

VerifyRegistry::registerVerifyingMethods(verifyingMethods: $verifierMethodsRsaPhpSeclib4);
