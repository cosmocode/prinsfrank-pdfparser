<?php declare(strict_types=1);

namespace PrinsFrank\PdfParser\Document\ContentStream\Command\Operator\State\Interaction;

use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\ContentStreamScope;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\PositionedTextElement;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TransformationMatrix;
use PrinsFrank\PdfParser\Exception\PdfParserException;

interface IncludesXObjects {
    /**
     * @param list<int> $visitedObjectIds
     * @throws PdfParserException
     * @return list<PositionedTextElement>
     */
    public function getPositionedTextElements(string $operands, ContentStreamScope $scope, TransformationMatrix $transformationMatrix, array $visitedObjectIds): array;
}
