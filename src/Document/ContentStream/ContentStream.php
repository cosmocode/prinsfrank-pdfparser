<?php
declare(strict_types=1);

namespace PrinsFrank\PdfParser\Document\ContentStream;

use PrinsFrank\PdfParser\Document\ContentStream\Command\ContentStreamCommand;
use PrinsFrank\PdfParser\Document\ContentStream\Command\Operator\State\GraphicsStateOperator;
use PrinsFrank\PdfParser\Document\ContentStream\Command\Operator\State\Interaction\InteractsWithTransformationMatrix;
use PrinsFrank\PdfParser\Document\ContentStream\Command\Operator\State\Interaction\InteractsWithTextState;
use PrinsFrank\PdfParser\Document\ContentStream\Command\Operator\State\Interaction\ProducesPositionedTextElements;
use PrinsFrank\PdfParser\Document\ContentStream\Object\TextObject;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\GraphicsState;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\LineGroupingStrategy\LineGroupingStrategy;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\PositionedTextElement;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TransformationMatrix;
use PrinsFrank\PdfParser\Document\Dictionary\ResourceDictionaryChain;
use PrinsFrank\PdfParser\Document\Document;
use PrinsFrank\PdfParser\Document\Object\Decorator\Page;
use PrinsFrank\PdfParser\Exception\ParseFailureException;
use PrinsFrank\PdfParser\Exception\PdfParserException;

/** @api */
readonly class ContentStream {
    public const WORD_BREAK_THRESHOLD = 0.25;

    /** @var list<TextObject|ContentStreamCommand> */
    public array $content;

    /** @no-named-arguments */
    public function __construct(
        TextObject|ContentStreamCommand... $content,
    ) {
        $this->content = $content;
    }

    /**
     * Return every run of text shown in this content stream, each with its position on the page. The names it shows
     * text in (/F4, ...) resolve lazily after the walk against the $resourceChain, so it is stamped onto each
     * element's text state here.
     *
     * @throws PdfParserException
     * @return list<PositionedTextElement>
     */
    public function getPositionedTextElements(?ResourceDictionaryChain $resourceChain = null): array {
        // The resolution chain is constant for the whole stream, so it is stamped onto the text state once here and
        // carried unchanged through every later state change rather than threaded into each element; getFont() resolves
        // the font lazily against it after the walk.
        $state = GraphicsState::initial();
        $state = $state->withTextState($state->textState->withResourceChain($resourceChain ?? new ResourceDictionaryChain([])));
        $positionedTextElements = $stack = [];
        foreach ($this->content as $content) {
            if ($content instanceof ContentStreamCommand) {
                if ($content->operator === GraphicsStateOperator::SaveCurrentStateToStack) {
                    $stack[] = $state;
                } elseif ($content->operator === GraphicsStateOperator::RestoreMostRecentStateFromStack) {
                    $state = array_pop($stack) ?? throw new ParseFailureException();
                }

                if ($content->operator instanceof InteractsWithTextState) {
                    $state = $state->withTextState($content->operator->applyToTextState($content->operands, $state->textState));
                }

                if ($content->operator instanceof InteractsWithTransformationMatrix) {
                    $state = $state->withCtm($content->operator->applyToTransformationMatrix($content->operands, $state->ctm, $state->textState));
                }

                continue;
            }

            $textMatrix = new TransformationMatrix(1, 0, 0, 1, 0, 0); // Identity matrix, See Table 106, Tm operator for initial value in text object
            foreach ($content->contentStreamCommands as $contentStreamCommand) {
                if ($contentStreamCommand->operator instanceof InteractsWithTextState) {
                    $state = $state->withTextState($contentStreamCommand->operator->applyToTextState($contentStreamCommand->operands, $state->textState));
                }

                if ($contentStreamCommand->operator instanceof InteractsWithTransformationMatrix) {
                    $textMatrix = $contentStreamCommand->operator->applyToTransformationMatrix($contentStreamCommand->operands, $textMatrix, $state->textState);
                }

                if ($contentStreamCommand->operator instanceof ProducesPositionedTextElements) {
                    $positionedTextElements[] = $contentStreamCommand->operator->getPositionedTextElement($contentStreamCommand->operands, $textMatrix, $state->ctm, $state->textState);
                }
            }
        }

        return $positionedTextElements;
    }

    /** @throws PdfParserException */
    public function getText(Document $document, Page $page, LineGroupingStrategy $lineGroupingStrategy): string {
        $text = '';
        $isFirstLine = true;
        $positionedTextElements = $this->getPositionedTextElements($page->getResourceChain());
        foreach ($lineGroupingStrategy->group($positionedTextElements) as $positionedTextElementsForLine) {
            if (!$isFirstLine) {
                $text .= "\n";
            }

            $isFirstLine = false;
            $previousTextElementOnLine = null;
            foreach ($positionedTextElementsForLine as $positionedTextElement) {
                $elementText = $positionedTextElement->getText($document);
                if ($elementText === '') {
                    $previousTextElementOnLine = $positionedTextElement;
                    continue;
                }

                if (
                    $previousTextElementOnLine !== null
                    && $lineGroupingStrategy->requiresSpaceBetween($previousTextElementOnLine, $positionedTextElement, $document)
                    && str_ends_with($text, ' ') === false
                    && str_starts_with($elementText, ' ') === false
                ) {
                    $text .= ' ';
                }

                $text .= $elementText;
                $previousTextElementOnLine = $positionedTextElement;
            }
        }

        return $text;
    }
}
