<?php declare(strict_types=1);

namespace PrinsFrank\PdfParser\Tests\Unit\Extraction\TextGrouping;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\PositionedTextElement;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TextSegment\TextSegment;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TextState;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TransformationMatrix;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\Vector;
use PrinsFrank\PdfParser\Document\Dictionary\DictionaryValue\TextString\LiteralStringValue;
use PrinsFrank\PdfParser\Extraction\TextGrouping\BaselineFrame;

#[CoversClass(BaselineFrame::class)]
class BaselineFrameTest extends TestCase {
    public function testPageFrameUsesPageAxes(): void {
        $frame = BaselineFrame::page();
        $element = new PositionedTextElement([new TextSegment(null, new LiteralStringValue('a'))], new TransformationMatrix(1, 0, 0, 1, 100, 700), new TextState(null, 10));

        static::assertSame(100.0, $frame->along($element));
        static::assertSame(700.0, $frame->across($element));
    }

    public function testFrameOfTextRunningUpThePage(): void {
        // The baseline points up the page, so the line above lies to the left.
        $frame = new BaselineFrame(new Vector(0.0, 1.0));
        $element = new PositionedTextElement([new TextSegment(null, new LiteralStringValue('a'))], new TransformationMatrix(0, 1, -1, 0, 100, 700), new TextState(null, 10));

        static::assertSame(700.0, $frame->along($element));
        static::assertSame(-100.0, $frame->across($element));
    }
}
