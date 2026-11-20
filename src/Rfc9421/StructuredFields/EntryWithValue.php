<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\Signer\Signatures\Line;

/**
 * Replacement for Entry
 *
 * NOTE: '\' line wrapping per RFC 8792
 *
 * "@status": 503
 * "content-digest": sha-512=:0Y6iCBzGg5rZtoXS95Ijz03mslf6KAMCloESHObf\
 * wnHJDbkkWWQz6PhhU9kxsTbARtY2PTBOzq24uJFpHsMuAg==:
 * "content-type": application/json
 * "@authority";asReq: example.com
 * "@method";asReq: POST
 * "@path";asReq: /foo
 * "content-digest";asReq: sha-512=:WZDPaVn/7XgHaAy8pmojAkGWoRx2UFChF41A\
 * 2svX+TaPm+AbwAgBWnrIiYllu7BNNyealdVLvRwEmTHWXvJwew==:
 * "@signature-params": ("@status" "content-digest" "content-type" \
 * "@authority";asReq "@method";asReq "@path";asReq "content-digest";asReq)\
 * ;created=1618884479;keyid="test-key-ecc-p256"
 *
 * Makes the line with there values they are in the signature-input.
 * It does NOT processing an header-value or something. Only the Signature-Param Key
 * todo check logic if there are more initial contents params
 * Formats everything except the value
 * something like "test-header";sf: VALUE
 * Todo this is an helper class which only compiles the component-fieldName
 */
final class EntryWithValue
{
    private function __construct(private readonly ComponentBuilder $builder, private null|string|int $value = null)
    {
    }

    public static function fromBuilder(ComponentBuilder $builder, null|string|int $value = null): self
    {
        return new self($builder, $value);
    }

    /**
     * String Lower case without InitialContentsEnums
     * Todo maybe from Bakame Item it is more precise
     *
     */
    public function getFieldName(): string
    {
        return $this->builder->getFieldNameString();
    }

    public function getBuilder(): ComponentBuilder
    {
        return $this->builder;
    }

    public function setValue(string|int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function parseComponentNameAndValue(): string
    {
        return $this->getComponent().Line::colonWithSpace.$this->getValue();
    }

    /**
     * With Quotes "example-dict" or "example-dict";sf
     *
     * @return string "example-dict";sf
     */
    public function getComponent(): string
    {
        return (string)$this->builder;
    }

    public function getValue(): null|int|string
    {
        return $this->value;
    }

}
