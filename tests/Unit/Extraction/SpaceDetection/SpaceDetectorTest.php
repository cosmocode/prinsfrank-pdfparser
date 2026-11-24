<?php declare(strict_types=1);

namespace PrinsFrank\PdfParser\Tests\Unit\Extraction\SpaceDetection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\PositionedTextElement;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TextSegment\TextSegment;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TextState;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TransformationMatrix;
use PrinsFrank\PdfParser\Document\Dictionary\Decorator\Font;
use PrinsFrank\PdfParser\Document\Dictionary\DictionaryValue\TextString\LiteralStringValue;
use PrinsFrank\PdfParser\Extraction\SpaceDetection\SpaceDetector;

#[CoversClass(SpaceDetector::class)]
class SpaceDetectorTest extends TestCase {
    public function testMeasuresTheGapAlongAVerticalBaseline(): void {
        // The baseline points down the page. The advance is 3 and the threshold is 10 * 0.25 = 2.5.
        $font = self::createStub(Font::class);
        $font->method('getWidthForBytes')->willReturn(3.0);

        $previous = new PositionedTextElement([new TextSegment(null, new LiteralStringValue('A'))], new TransformationMatrix(0, -1, 1, 0, 300, 700), new TextState($font, 10));
        $farBelow = new PositionedTextElement([new TextSegment(null, new LiteralStringValue('B'))], new TransformationMatrix(0, -1, 1, 0, 300, 600), new TextState($font, 10));
        $justBelow = new PositionedTextElement([new TextSegment(null, new LiteralStringValue('B'))], new TransformationMatrix(0, -1, 1, 0, 300, 695), new TextState($font, 10));

        static::assertTrue(SpaceDetector::shouldContainExtraSpace($farBelow, $previous));
        static::assertFalse(SpaceDetector::shouldContainExtraSpace($justBelow, $previous));
    }
}
