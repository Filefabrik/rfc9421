<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use Closure;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Support\UnquoteHelper;

// in response the req parameter
/**
 * todo the content-digest;req and the content-digest regular
 * NOTE: '\' line wrapping per RFC 8792
 *
 * "@status": 503
 * "content-digest": sha-512=:0Y6iCBzGg5rZtoXS95Ijz03mslf6KAMCloESHObf\
 * wnHJDbkkWWQz6PhhU9kxsTbARtY2PTBOzq24uJFpHsMuAg==:
 * "content-type": application/json
 * "@authority";req: example.com
 * "@method";req: POST
 * "@path";req: /foo
 * "content-digest";req: sha-512=:WZDPaVn/7XgHaAy8pmojAkGWoRx2UFChF41A\
 * 2svX+TaPm+AbwAgBWnrIiYllu7BNNyealdVLvRwEmTHWXvJwew==:
 * "@signature-params": ("@status" "content-digest" "content-type" \
 * "@authority";req "@method";req "@path";req "content-digest";req)\
 * ;created=1618884479;keyid="test-key-ecc-p256"
 */
/**
 * Makes the line with there values they are in the signature-input
 * todo check logic with req
 * Formats everything except the value
 * something like "test-header";sf: VALUE
 */
final class Entry
{
    public const string colonWithSpace = ': ';

    public const string semicolon = ';';

    public const string equals = '=';

    /**
     * @var array{Closure}|array{}
     */
    private(set) array $keyMethods = [];

    private bool $reqEnum = false;

    public function __construct(
        /**
         * string without quotes and lower case
         * todo check @query-param with lower case
         */
        private(set) readonly string $key,
        /**
         * The value will not be formatted
         * todo check specs which types are valid
         *
         * @var string|int|null int while content length
         *
         */
        private(set) readonly null|string|int $value = null,
        private(set) null|InitialContentsEnum $initialContents = null,
    ) {
    }

    public function setInitialContents(InitialContentsEnum $initialContents): self
    {
        // todo check there was already a flag
        $this->initialContents = $initialContents;

        return $this;
    }

    public function setValue(string $value): self
    {
        $new = new self($this->key, $value, $this->initialContents);
        if ($this->reqEnum) {
            $new->withREQEnum();
        }

        // todo check the value was already set and if it is allowed to re-modularize the value

        return $new;
    }

    /**
     * todo check what happens with req
     */
    public function withREQEnum(): self
    {
        $this->reqEnum = true;

        return $this;
    }

    /**
     * Special method for pa
     *

     */
    public function withKeyMethod(Closure $method): self
    {
        $this->keyMethods[] = $method;

        return $this;
    }

    /**
     * Todo Extra Parser from Outside
     * Parsing the expression for signature component
     */
    public function parse(): string
    {
        $parts = [];

        // the key quoted
        $parts[] = $this->parseKeyMethods();

        /*
         * @see https://www.rfc-editor.org/info/rfc9421/#section-2.1.3
         * todo bs allowed with some byte sequences (base_encode64)
         * Example-Header: value, with, lots
         * Example-Header: of, commas
         * "example-header";bs: :dmFsdWUsIHdpdGgsIGxvdHM=:, :b2YsIGNvbW1hcw==:
         */
        /*
         * @see https://www.rfc-editor.org/info/rfc9421/#section-2.1.1-8
         * "However, if the sf parameter is added, the value is reserialized as follows:"
         * "example-dict";sf: a=1, b=2;x=1;y=2, c=(a b c)
         * "example-header";bs: :dmFsdWUsIHdpdGgsIGxvdHMsIG9mLCBjb21tYXM=:
         */
        if ($this->initialContents instanceof InitialContentsEnum) {
            // todo check req
            $parts[] = self::semicolon;
            $parts[] = $this->initialContents->name;

            if (InitialContentsEnum::sf === $this->initialContents || InitialContentsEnum::bs === $this->initialContents) {
                $parts[] = self::colonWithSpace;
            } elseif (InitialContentsEnum::key === $this->initialContents) {
                // todo check "name" has to be parsed via a special method InitialContentsEnum::name === $this->initialContents
                $parts[] = self::equals;
            }
        } else {
            $parts[] = self::colonWithSpace;
        }

        $parts[] = $this->value;

        return implode('', $parts);
    }

    /**
     * For @query-param use a special method
     */
    private function parseKeyMethods(): string
    {
        if (0 === count($this->keyMethods)) {
            return UnquoteHelper::quote($this->key);
        }

        // iterate for key
        // todo cover error
        $key = $this->key;
        foreach ($this->keyMethods as $keyMethod) {
            $key = $keyMethod($key);
        }

        return $key;
    }
}
