<?php
/*
 * Copyright (c) 2026. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/* Copyright */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Examples\Tools;

use JsonException;

final class JsonToString
{
    public static function toString(array $data): ?string
    {
        try {
            $bodyJson = json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            echo $jsonException->getMessage();

            return null;
        }

        return $bodyJson;
    }
}
