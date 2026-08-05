<?php declare(strict_types=1);

namespace PrinsFrank\PdfParser\Document\ContentStream\Command\Operator\State;

use Override;
use PrinsFrank\PdfParser\Document\ContentStream\Command\Operator\State\Interaction\IncludesXObjects;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\ContentStreamScope;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\GraphicsState;
use PrinsFrank\PdfParser\Document\Dictionary\DictionaryKey\DictionaryKey;
use PrinsFrank\PdfParser\Document\Dictionary\DictionaryKey\ExtendedDictionaryKey;
use PrinsFrank\PdfParser\Document\Object\Decorator\XObject;

/**
 * @internal
 *
 * @specification table 86 - XObject operator
 */
enum XObjectOperator: string implements IncludesXObjects {
    case Paint = 'Do';

    #[Override]
    public function getPositionedTextElements(string $operands, ContentStreamScope $scope, GraphicsState $state): array {
        $reference = $scope->resolve(DictionaryKey::XOBJECT, ExtendedDictionaryKey::fromKeyString($operands));
        if ($reference === null || $scope->isPainting($reference->objectNumber)) {
            return []; // the name is not in scope, or the form it names is already being painted further up
        }

        $xObject = $scope->document->getObject($reference->objectNumber, XObject::class);
        if ($xObject === null || $xObject->isForm() === false) {
            return [];
        }

        return $xObject->getPositionedTextElements($scope->painting($reference->objectNumber), $state);
    }
}
