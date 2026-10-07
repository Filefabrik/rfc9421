<?php

declare(strict_types=1);

use Filefabrik\Rfc9421\Cryptography\SignatureRegistry;
use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Cryptography\VerifyRegistry;
use phpseclib4\Crypt\EC;
use phpseclib4\Crypt\RSA;

require_once dirname(__DIR__).'/vendor/autoload.php';

/**
 * todo: not fully implemented and mixed with OpenSSL
 * Sender-Side
 * https://toolv.com/en/app/rsa-key-generator
 */

/**
 * signer config
 */
$signingMethodsRsaPhpSeclib4 = [
    //phpseclib4
    SigningEnum::rsa_pss_sha512->name    => function(string $signingDatas, $privateKeyPem) {
        return RSA::loadPrivateKey($privateKeyPem)
                  ->withHash('sha512')
                  ->withPadding(RSA::SIGNATURE_PSS)
                  ->sign($signingDatas)
        ;
    },
    //phpseclib4
    SigningEnum::rsa_v_1_5_sha_256->name => function(string $signingDatas, $privateKeyPem) {
        return RSA::loadPrivateKey($privateKeyPem)
                  ->withHash('sha256')
                  ->withPadding(RSA::SIGNATURE_PKCS1)
                  ->sign($signingDatas)
        ;
    },
    //phpseclib4
    SigningEnum::rsa_v_1_5_sha_384->name => function(string $signingDatas, $privateKeyPem) {
        return RSA::loadPrivateKey($privateKeyPem)
                  ->withHash('sha384')
                  ->withPadding(RSA::SIGNATURE_PKCS1)
                  ->sign($signingDatas)
        ;
    },
    //phpseclib4
    SigningEnum::rsa_v_1_5_sha_512->name => function(string $signingDatas, $privateKeyPem) {
        return RSA::loadPrivateKey($privateKeyPem)
                  ->withHash('sha512')
                  ->withPadding(RSA::SIGNATURE_PKCS1)
                  ->sign($signingDatas)
        ;
    },
    // native php
    SigningEnum::hmac_sha_256->name      => function(string $signingDatas, $privateKeyPem) {
        return hash_hmac('sha256', $signingDatas, $privateKeyPem, true);
    },
    // native php
    SigningEnum::hmac_sha_384->name      => function(string $signingDatas, $privateKeyPem) {
        return hash_hmac('sha384', $signingDatas, $privateKeyPem, true);
    },
    // native php
    SigningEnum::hmac_sha_512->name      => function(string $signingDatas, $privateKeyPem) {
        return hash_hmac('sha512', $signingDatas, $privateKeyPem, true);
    },
    //phpseclib4
    SigningEnum::ed25519->name           => function(string $signingDatas, $privateKeyPem) {
        return EC::loadPrivateKey($privateKeyPem)
                 ->sign($signingDatas)
        ;
    },
];

/** @noinspection PhpUnhandledExceptionInspection */
SignatureRegistry::registerMethods(methods: $signingMethodsRsaPhpSeclib4);

/**
 * Verifier config
 */

$verifierMethodsRsaPhpSeclib4 = [
    //phpseclib4
    SigningEnum::rsa_pss_sha512->name    => function(string $signingDatas, $signature, $publicKeyPem) {
        return RSA::loadPublicKey($publicKeyPem)
                  ->withHash('sha512')
                  ->withPadding(RSA::SIGNATURE_PSS)
                  ->verify($signingDatas, $signature)
        ;
    },
    //phpseclib4
    SigningEnum::rsa_v_1_5_sha_256->name => function(string $signingDatas, $signature, $publicKeyPem) {
        return RSA::loadPublicKey($publicKeyPem)
                  ->withHash('sha256')
                  ->withPadding(RSA::SIGNATURE_PKCS1)
                  ->verify($signingDatas, $signature)
        ;
    },
    //phpseclib4
    SigningEnum::rsa_v_1_5_sha_384->name => function(string $signingDatas, $signature, $publicKeyPem) {
        return RSA::loadPublicKey($publicKeyPem)
                  ->withHash('sha384')
                  ->withPadding(RSA::SIGNATURE_PKCS1)
                  ->verify($signingDatas, $signature)
        ;
    },
    //phpseclib4
    SigningEnum::rsa_v_1_5_sha_512->name => function(string $signingDatas, $signature, $publicKeyPem) {
        return RSA::loadPublicKey($publicKeyPem)
                  ->withHash('sha512')
                  ->withPadding(RSA::SIGNATURE_PKCS1)
                  ->verify($signingDatas, $signature)
        ;
    },
    // native php
    SigningEnum::hmac_sha_256->name      => function(string $signingDatas, $signature, $publicKeyPem) {
        return hash_equals(hash_hmac('sha256', $signingDatas, $publicKeyPem, true), $signature);
    },
    // native php
    SigningEnum::hmac_sha_384->name      => function(string $signingDatas, $signature, $publicKeyPem) {
        return hash_equals(hash_hmac('sha384', $signingDatas, $publicKeyPem, true), $signature);
    },
    // native php
    SigningEnum::hmac_sha_512->name      => function(string $signingDatas, $signature, $publicKeyPem) {
        return hash_equals(hash_hmac('sha512', $signingDatas, $publicKeyPem, true), $signature);
    },
    //phpseclib4
    SigningEnum::ed25519->name           => function(string $signingDatas, $signature, $publicKeyPem) {
        return EC::loadPublicKey($publicKeyPem)
                 ->verify($signingDatas, $signature)
        ;
    },
];

/** @noinspection PhpUnhandledExceptionInspection */
VerifyRegistry::registerMethods(methods: $verifierMethodsRsaPhpSeclib4);
