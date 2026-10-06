<?php

declare(strict_types=1);

use Filefabrik\Rfc9421\Cryptography\SignatureRegistry;
use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Cryptography\VerifyRegistry;

$signings = [
    // php native openssl
    SigningEnum::rsa_pss_sha512->name      => function(string $signingDatas, $privateKeyPem): string {
        if (PHP_VERSION < 8.5) {
            throw new BadMethodCallException(
                'rsa_pss_sha512 needs PHP 8.5+. Please see https://www.php.net/manual/en/openssl.padding.php#constant.openssl-pkcs1-pss-padding',
            );
        }
        $signature = '';

        openssl_sign(
            $signingDatas,
            $signature,
            $privateKeyPem,
            OPENSSL_ALGO_SHA512,
            OPENSSL_PKCS1_PSS_PADDING,
        );

        return $signature;
    },
    // php native openssl
    SigningEnum::rsa_v_1_5_sha_256->name   => function(string $signingDatas, $privateKeyPem): string {
        $signature = '';

        openssl_sign(
            data       : $signingDatas,
            signature  : $signature,
            private_key: $privateKeyPem,
            algorithm  : OPENSSL_ALGO_SHA256,
        );

        return $signature;
    },
    // php native openssl
    SigningEnum::rsa_v_1_5_sha_384->name   => function(string $signingDatas, $privateKeyPem): string {
        $signature = '';

        openssl_sign(
            data       : $signingDatas,
            signature  : $signature,
            private_key: $privateKeyPem,
            algorithm  : OPENSSL_ALGO_SHA384,
        );

        return $signature;
    },
    // php native openssl
    SigningEnum::rsa_v_1_5_sha_512->name   => function(string $signingDatas, $privateKeyPem): string {
        $signature = '';

        openssl_sign(
            data       : $signingDatas,
            signature  : $signature,
            private_key: $privateKeyPem,
            algorithm  : OPENSSL_ALGO_SHA512,
        );

        return $signature;
    },
    // native php
    SigningEnum::hmac_sha_256->name        => function(string $signingDatas, $privateKeyPem): string {
        return hash_hmac('sha256', $signingDatas, $privateKeyPem, true);
    },
    // native php
    SigningEnum::hmac_sha_384->name        => function(string $signingDatas, $privateKeyPem): string {
        return hash_hmac('sha384', $signingDatas, $privateKeyPem, true);
    },
    // native php
    SigningEnum::hmac_sha_512->name        => function(string $signingDatas, $privateKeyPem): string {
        return hash_hmac('sha512', $signingDatas, $privateKeyPem, true);
    },
    // php native openssl
    SigningEnum::ecdsa_p_256_sha_256->name => function(string $signingDatas, $privateKeyPem): string {
        $signature = '';
        openssl_sign($signingDatas, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);

        return $signature;
    },
    // php native openssl
    SigningEnum::ecdsa_p_384_sha_384->name => function(string $signingDatas, $privateKeyPem): string {
        $signature = '';
        openssl_sign($signingDatas, $signature, $privateKeyPem, OPENSSL_ALGO_SHA384);

        return $signature;
    },
    // php native openssl
    SigningEnum::ed25519->name             => function(string $signingDatas, $privateKeyPem): string {
        $signature = '';
        $secretKey = openssl_pkey_get_private($privateKeyPem);

        if (!openssl_sign($signingDatas, $signature, $secretKey, 0)) {
            throw new RuntimeException('Signing failed: '.openssl_error_string());
        }

        return $signature;
    },

];

SignatureRegistry::registerMethods(methods: $signings);

$verifying = [
    // php native openssl
    SigningEnum::rsa_pss_sha512->name      => function(string $signingDatas, $signature, $publicKeyPem): bool {
        if (PHP_VERSION < 8.5) {
            throw new BadMethodCallException(
                'rsa_pss_sha512 needs PHP 8.5+. Please see https://www.php.net/manual/en/openssl.padding.php#constant.openssl-pkcs1-pss-padding',
            );
        }

        return 0 < openssl_verify(
            $signingDatas,
            $signature,
            $publicKeyPem,
            OPENSSL_ALGO_SHA512,
            OPENSSL_PKCS1_PSS_PADDING,
        );
    },
    // php native openssl
    SigningEnum::rsa_v_1_5_sha_256->name   => function(string $signingDatas, $signature, $publicKeyPem): bool {
        return 0 < openssl_verify(
            $signingDatas,
            $signature,
            $publicKeyPem,
            OPENSSL_ALGO_SHA256,
        );
    },
    // php native openssl
    SigningEnum::rsa_v_1_5_sha_384->name   => function(string $signingDatas, $signature, $publicKeyPem): bool {
        return 0 < openssl_verify(
            $signingDatas,
            $signature,
            $publicKeyPem,
            OPENSSL_ALGO_SHA384,
        );
    },
    // php native openssl
    SigningEnum::rsa_v_1_5_sha_512->name   => function(string $signingDatas, $signature, $publicKeyPem): bool {
        return 0 < openssl_verify(
            $signingDatas,
            $signature,
            $publicKeyPem,
            OPENSSL_ALGO_SHA512,
        );
    },
    // native php
    SigningEnum::hmac_sha_256->name        => function(string $signingDatas, $signature, $publicKeyPem): bool {
        return hash_equals(hash_hmac('sha256', $signingDatas, $publicKeyPem, true), $signature);
    },
    // native php
    SigningEnum::hmac_sha_384->name        => function(string $signingDatas, $signature, $publicKeyPem): bool {
        return hash_equals(hash_hmac('sha384', $signingDatas, $publicKeyPem, true), $signature);
    },
    // native php
    SigningEnum::hmac_sha_512->name        => function(string $signingDatas, $signature, $publicKeyPem): bool {
        return hash_equals(hash_hmac('sha512', $signingDatas, $publicKeyPem, true), $signature);
    },
    // php native openssl
    SigningEnum::ecdsa_p_256_sha_256->name => function(string $signingDatas, $signature, $publicKeyPem): bool {
        return 0 < openssl_verify($signingDatas, $signature, $publicKeyPem, OPENSSL_ALGO_SHA256);
    },
    // php native openssl
    SigningEnum::ecdsa_p_384_sha_384->name => function(string $signingDatas, $signature, $publicKeyPem): bool {
        return 0 < openssl_verify($signingDatas, $signature, $publicKeyPem, OPENSSL_ALGO_SHA384);
    },
    // php native openssl
    SigningEnum::ed25519->name             => function(string $signingDatas, $signature, $publicKeyPem): bool {
        return 0 < openssl_verify($signingDatas, $signature, openssl_pkey_get_public($publicKeyPem), 0);
    },
];

VerifyRegistry::registerMethods(methods: $verifying);
