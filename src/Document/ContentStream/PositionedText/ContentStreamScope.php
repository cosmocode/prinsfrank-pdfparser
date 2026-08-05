<?php declare(strict_types=1);

namespace PrinsFrank\PdfParser\Document\ContentStream\PositionedText;

use PrinsFrank\PdfParser\Document\Dictionary\Dictionary;
use PrinsFrank\PdfParser\Document\Dictionary\DictionaryKey\DictionaryKey;
use PrinsFrank\PdfParser\Document\Dictionary\DictionaryKey\ExtendedDictionaryKey;
use PrinsFrank\PdfParser\Document\Dictionary\DictionaryValue\Reference\ReferenceValue;
use PrinsFrank\PdfParser\Document\Dictionary\ResourceDictionaryChain;
use PrinsFrank\PdfParser\Document\Document;

/**
 * The context for walking a content stream that the stream itself does not carry: the document its named references
 * resolve against and the {@see ResourceDictionaryChain} those names are looked up in. Unlike {@see GraphicsState} it
 * stays constant for one stream, changing only when the walk descends into a Form XObject.
 *
 * @internal
 */
final readonly class ContentStreamScope {
    public function __construct(
        public Document $document,
        public ResourceDictionaryChain $resourceChain,
    ) {}

    /** The scope inside a painted Form XObject: its own /Resources is prepended onto the inherited chain, shadowing it. */
    public function forForm(?Dictionary $formResources): self {
        return new self(
            $this->document,
            $formResources === null ? $this->resourceChain : $this->resourceChain->prepend($formResources),
        );
    }

    /** The reference a name appearing in the stream points at, or null when no dictionary in scope defines it. */
    public function resolve(DictionaryKey $resourceType, DictionaryKey|ExtendedDictionaryKey $name): ?ReferenceValue {
        return $this->resourceChain->resolve($this->document, $resourceType, $name);
    }
}
