<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use Bakame\Http\StructuredFields\Dictionary;
use Bakame\Http\StructuredFields\InnerList;
use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Dto\HeaderItemsDto;
use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use Filefabrik\Rfc9421\SigningConfiguration;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Destructing and recompile the whole signature ("") expression
 */
final readonly class DestructHeaders
{
    public function __construct(
        /**
         * HeaderLines key:value we are compiling the signed component from
         */
        private(set) HeaderItemsDto $headerItemsDto,
        /**
         * ("example-dict" "some-thing;req")
         */
        private(set) string $signatureParamsLine,
        private(set) RequestInterface|ResponseInterface $message,
        /**
         * only need if response signing
         *
         */
        private(set) RequestInterface $originalRequest,
        private(set) SigningConfiguration $signingConfiguration,
    ) {
    }

    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @return array<string> all lines they are proceeded
     */
    public function start(): array
    {
        $signatureComponents = [];
        $processedComponents = [];
        $dict                = $this->parseStructuredDict($this->signatureParamsLine);
        if ($dict->isNotEmpty()) {
            /** @var \Bakame\Http\StructuredFields\Item $item */
            foreach ($dict as $item) {
                $httpValue = $item->toHttpValue();
                if (in_array($httpValue, $processedComponents, true)) {
                    throw new UnProcessableSignatureException(sprintf('Duplicate item found in singable components "%s"', $httpValue));
                }

                $processedComponents[] = $httpValue;

                $instance = new CompileEntry(
                    messageInterface    : $this->message,
                    signingConfiguration: $this->signingConfiguration,
                    componentItemDto    : ComponentItemDto::fromFieldItem($item),
                    headerItemsDto      : $this->headerItemsDto,
                );

                // todo check what happens with null
                $parsed                = $instance->handle()
                                                  ->parse()
                ;
                $signatureComponents[] = $parsed;
            }
        }

        return $signatureComponents;
    }

    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     */
    private function parseStructuredDict(string $headerValue): Dictionary|InnerList
    {
        $trimmedHeaderValue = trim($headerValue);

        return str_starts_with($trimmedHeaderValue, '(') && str_ends_with($trimmedHeaderValue, ')') ?
            InnerList::fromHttpValue($headerValue) :
            Dictionary::fromHttpValue($headerValue);
    }

}
