<?php declare(strict_types=1);

namespace PrinsFrank\PdfParser\Tests\Unit\Document\ContentStream\PositionedText;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\PositionedTextElement;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TextSegment\TextSegment;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TextState;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TransformationMatrix;
use PrinsFrank\PdfParser\Document\Dictionary\DictionaryValue\TextString\LiteralStringValue;

#[CoversClass(PositionedTextElement::class)]
class PositionedTextElementTest extends TestCase {
    public function testGetHeightForHorizontalText(): void {
        static::assertSame(
            10.0,
            (new PositionedTextElement([new TextSegment(null, new LiteralStringValue('a'))], new TransformationMatrix(1, 0, 0, 1, 0, 0), new TextState(null, 10)))->getHeight(),
        );
    }

    public function testGetHeightUsesVerticalScale(): void {
        static::assertSame(
            30.0,
            (new PositionedTextElement([new TextSegment(null, new LiteralStringValue('a'))], new TransformationMatrix(2, 0, 0, 3, 0, 0), new TextState(null, 10)))->getHeight(),
        );
    }

    public function testGetHeightForTextOnAVerticalBaseline(): void {
        static::assertSame(
            10.0,
            (new PositionedTextElement([new TextSegment(null, new LiteralStringValue('a'))], new TransformationMatrix(0, -1, 1, 0, 0, 0), new TextState(null, 10)))->getHeight(),
        );
    }
}
