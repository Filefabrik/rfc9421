<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

use Filefabrik\Rfc9421\Cryptography\SignatureRegistry;
use Filefabrik\Rfc9421\Cryptography\VerifyRegistry;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use phpseclib4\Crypt\EC;
use phpseclib4\Crypt\RSA;

require_once __DIR__.'/../vendor/autoload.php';

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
    SigningEnum::rsa_pss_sha512->name    => fn (string $signingDatas, string|array $privateKeyPem) => RSA::loadPrivateKey($privateKeyPem)
              ->withHash('sha512')
              ->withPadding(RSA::SIGNATURE_PSS)
              ->sign($signingDatas),
    //phpseclib4
    SigningEnum::rsa_v_1_5_sha_256->name => fn (string $signingDatas, string|array $privateKeyPem) => RSA::loadPrivateKey($privateKeyPem)
              ->withHash('sha256')
              ->withPadding(RSA::SIGNATURE_PKCS1)
              ->sign($signingDatas),
    //phpseclib4
    SigningEnum::rsa_v_1_5_sha_384->name => fn (string $signingDatas, string|array $privateKeyPem) => RSA::loadPrivateKey($privateKeyPem)
              ->withHash('sha384')
              ->withPadding(RSA::SIGNATURE_PKCS1)
              ->sign($signingDatas),
    //phpseclib4
    SigningEnum::rsa_v_1_5_sha_512->name => fn (string $signingDatas, string|array $privateKeyPem) => RSA::loadPrivateKey($privateKeyPem)
              ->withHash('sha512')
              ->withPadding(RSA::SIGNATURE_PKCS1)
              ->sign($signingDatas),
    // native php
    SigningEnum::hmac_sha_256->name      => fn (string $signingDatas, $privateKeyPem): string => hash_hmac('sha256', $signingDatas, $privateKeyPem, true),
    // native php
    SigningEnum::hmac_sha_384->name      => fn (string $signingDatas, $privateKeyPem): string => hash_hmac('sha384', $signingDatas, $privateKeyPem, true),
    // native php
    SigningEnum::hmac_sha_512->name      => fn (string $signingDatas, $privateKeyPem): string => hash_hmac('sha512', $signingDatas, $privateKeyPem, true),
    //phpseclib4
    SigningEnum::ed25519->name           => fn (string $signingDatas, string|array $privateKeyPem): string|array => EC::loadPrivateKey($privateKeyPem)
             ->sign($signingDatas),
];

/* @noinspection PhpUnhandledExceptionInspection */
SignatureRegistry::registerMethods(methods: $signingMethodsRsaPhpSeclib4);

/**
 * Verifier config
 */
$verifierMethodsRsaPhpSeclib4 = [
    //phpseclib4
    SigningEnum::rsa_pss_sha512->name    => fn (string $signingDatas, $signature, string|array $publicKeyPem) => RSA::loadPublicKey($publicKeyPem)
              ->withHash('sha512')
              ->withPadding(RSA::SIGNATURE_PSS)
              ->verify($signingDatas, $signature),
    //phpseclib4
    SigningEnum::rsa_v_1_5_sha_256->name => fn (string $signingDatas, $signature, string|array $publicKeyPem) => RSA::loadPublicKey($publicKeyPem)
              ->withHash('sha256')
              ->withPadding(RSA::SIGNATURE_PKCS1)
              ->verify($signingDatas, $signature),
    //phpseclib4
    SigningEnum::rsa_v_1_5_sha_384->name => fn (string $signingDatas, $signature, string|array $publicKeyPem) => RSA::loadPublicKey($publicKeyPem)
              ->withHash('sha384')
              ->withPadding(RSA::SIGNATURE_PKCS1)
              ->verify($signingDatas, $signature),
    //phpseclib4
    SigningEnum::rsa_v_1_5_sha_512->name => fn (string $signingDatas, $signature, string|array $publicKeyPem) => RSA::loadPublicKey($publicKeyPem)
              ->withHash('sha512')
              ->withPadding(RSA::SIGNATURE_PKCS1)
              ->verify($signingDatas, $signature),
    // native php
    SigningEnum::hmac_sha_256->name      => fn (string $signingDatas, $signature, $publicKeyPem): bool => hash_equals(hash_hmac('sha256', $signingDatas, $publicKeyPem, true), $signature),
    // native php
    SigningEnum::hmac_sha_384->name      => fn (string $signingDatas, $signature, $publicKeyPem): bool => hash_equals(hash_hmac('sha384', $signingDatas, $publicKeyPem, true), $signature),
    // native php
    SigningEnum::hmac_sha_512->name      => fn (string $signingDatas, $signature, $publicKeyPem): bool => hash_equals(hash_hmac('sha512', $signingDatas, $publicKeyPem, true), $signature),
    //phpseclib4
    SigningEnum::ed25519->name           => fn (string $signingDatas, string|array $signature, string|array $publicKeyPem): bool => EC::loadPublicKey($publicKeyPem)
             ->verify($signingDatas, $signature),
];

/* @noinspection PhpUnhandledExceptionInspection */
VerifyRegistry::registerMethods(methods: $verifierMethodsRsaPhpSeclib4);
