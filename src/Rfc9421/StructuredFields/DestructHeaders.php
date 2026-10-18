<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use Bakame\Http\StructuredFields\Dictionary;
use Bakame\Http\StructuredFields\InnerList;
use Bakame\Http\StructuredFields\Item;
use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use JsonException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

final class DestructHeaders
{
    private(set) ?StructuredFields $structuredFieldTypes = null;

    /**
     * @param array<string,string> $lowerCasedHeaderLines
     */
    public function __construct(
        private(set) readonly array $lowerCasedHeaderLines,
        private(set) readonly string $signatureParamsLine,
        private(set) readonly MessageInterface $message,
        private(set) readonly RequestInterface $originalRequest,
        private(set) readonly MethodsMapper $methodsMapper,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getLowerCasedHeadersFromInterface(MessageInterface $interface): array
    {
        $headers = [];
        foreach ($interface->getHeaders() as $name => $values) {
            $headers[strtolower($name)] = implode(', ', $values);
        }

        return $headers;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws JsonException
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @return array<string> all lines they are proceeded
     */
    public function start(): array
    {
        $signatureComponents = [];
        $processedComponents = [];
        $dict                = $this->parseStructuredDict($this->signatureParamsLine);
        if ($dict->isNotEmpty()) {
            $coveredStructuredFields = $dict->__toString();
            $indices                 = $dict->indices();

            foreach ($indices as $index) {
                $member = $dict->getByIndex($index);
                if (!$member instanceof Item) {
                    throw new UnProcessableSignatureException('Index '.$index.' not found');
                }

                if (in_array($member, $processedComponents, true)) {
                    throw new UnProcessableSignatureException('Duplicate member found');
                }

                $processedComponents[] = $member;

                $instance = new DestructEntry($member, $this->methodsMapper, $this->getStructuredFields());

                $resultEntry = $instance->canonicalizeComponent($this->lowerCasedHeaderLines, $this->message);

                $parsed                = $resultEntry->parse();
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

    /**
     * @throws JsonException
     */
    private function getStructuredFields(): StructuredFields
    {
        return $this->structuredFieldTypes ??= StructuredFields::fromDefaultList();
    }

    /**
     * @throws JsonException
     */
    public function setStructuredFieldType(string $key, string $value): self
    {
        $this->getStructuredFields()
             ->add($key, $value)
        ;

        return $this;
    }

}
