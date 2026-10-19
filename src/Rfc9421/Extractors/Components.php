<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Extractors;

use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Signature\Component;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\Support\PreparedMethods;

/**
 * Extracting the Signing Fields
 * Todo with structured fields extractor
 */
final readonly class Components
{
    /**
     * @param string $componentsString "content-length" "@query-param" "example-dict"
     */
    public function __construct(
        private(set) string $componentsString,
        private(set) ?PreparedMethods $preparedMethods = null,
    ) {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function extractToComponentsObject(): \Filefabrik\Rfc9421\Signature\Components
    {
        $components = new \Filefabrik\Rfc9421\Signature\Components();
        $parts      = explode(' ', $this->componentsString);
        foreach ($parts as $item) {
            $components->set($this->extractSegment($item));
        }

        return $components;
    }

    /**
     * Todo with structured fields extractor
     * Todo Make extract Method such as in PrepareComponents
     * 
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @example '"content-something";req' '"content-something"'
     *
     */
    public function extractSegment(string $segment): Component
    {
        // todo, if there an ";" but no following expression "InitialContentsEnum" throw

        if (($component = $this->callPreparedMethodIfExists($segment)) instanceof Component) {
            return $component;
        }

        $simple = '#^"(.*?)"$#';
        preg_match($simple, $segment, $matches);

        if (2 === count($matches)) {
            return new Component($matches[1]);
        }

        $s = implode('|', InitialContentsEnum::names());
        preg_match('#^"(.*?)";('.$s.')$#', $segment, $matches);

        if (3 === count($matches)) {
            return new Component($matches[1], $matches[2]);
        }

        throw new SignatureExtractionException(sprintf('Segment has unknown format "%s"', $segment));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    private function callPreparedMethodIfExists(string $segment): Component|false
    {
        if ($this->preparedMethods instanceof PreparedMethods) {
            $segmentName = $this->getSegmentName($segment);

            if ($this->preparedMethods->hasMethod($segmentName)) {
                // call the method
                $method = $this->preparedMethods->getMethod($segmentName);
                $result = $method->call($this, $segment, $segmentName);

                return new Component($segmentName, $result);
            }
        }

        // false, use default methods
        return false;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    private function getSegmentName(string $segment): string
    {
        $simple = '#^"(.*?)"#';
        preg_match($simple, $segment, $matches);

        if (2 !== count($matches)) {
            throw new SignatureExtractionException(sprintf('Segment has unknown format "%s"', $segment));
        }

        // todo validate against @ derived components
        return $matches[1];
    }
}
