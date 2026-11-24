<?php declare(strict_types=1);

namespace PrinsFrank\PdfParser\Tests\Unit\Extraction\TextGrouping;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\PositionedTextElement;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TextSegment\TextSegment;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TextState;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TransformationMatrix;
use PrinsFrank\PdfParser\Document\Dictionary\Decorator\Font;
use PrinsFrank\PdfParser\Document\Dictionary\DictionaryValue\TextString\LiteralStringValue;
use PrinsFrank\PdfParser\Extraction\TextGrouping\BaselineClusterStrategy;
use PrinsFrank\PdfParser\Extraction\TextGrouping\Block;

#[CoversClass(BaselineClusterStrategy::class)]
#[CoversClass(Block::class)]
class BaselineClusterStrategyTest extends TestCase {
    public function testGroupsHorizontalTextByVerticalOverlap(): void {
        $left = $this->element('left', new TransformationMatrix(1, 0, 0, 1, 10, 700));
        $right = $this->element('right', new TransformationMatrix(1, 0, 0, 1, 80, 700));
        $below = $this->element('below', new TransformationMatrix(1, 0, 0, 1, 10, 600));

        static::assertSame([[$left, $right], [$below]], BaselineClusterStrategy::group([$right, $below, $left]));
    }

    public function testKeepsAnEnclosedSubscriptOnItsLine(): void {
        $left = $this->element('CO', new TransformationMatrix(1, 0, 0, 1, 50, 100));
        $subscript = $this->element('2', new TransformationMatrix(1, 0, 0, 1, 60, 98), 7);
        $right = $this->element('-Bilanz', new TransformationMatrix(1, 0, 0, 1, 70, 100));

        static::assertSame([[$left, $subscript, $right]], BaselineClusterStrategy::group([$left, $right, $subscript]));
    }

    public function testReassemblesDiagonallyRotatedWordIntoOneLine(): void {
        $cos = cos(deg2rad(45));
        $sin = sin(deg2rad(45));
        $glyph = fn(int $i, string $text): PositionedTextElement => $this->element($text, new TransformationMatrix($cos, -$sin, $sin, $cos, 100 + $i * 8 * $cos, 100 - $i * 8 * $sin));
        $w = $glyph(0, 'W');
        $o = $glyph(1, 'O');
        $r = $glyph(2, 'R');

        static::assertSame([[$w, $o, $r]], BaselineClusterStrategy::group([$r, $w, $o]));
        // The first and last glyph are further apart in page Y than a glyph is tall.
        static::assertGreaterThan($w->getHeight(), abs($w->absoluteMatrix->offsetY - $r->absoluteMatrix->offsetY));
    }

    public function testReassemblesWordRotatedPast90Degrees(): void {
        $cos = cos(deg2rad(210));
        $sin = sin(deg2rad(210));
        $glyph = fn(int $i, string $text): PositionedTextElement => $this->element($text, new TransformationMatrix($cos, -$sin, $sin, $cos, 200 + $i * 8 * $cos, 200 - $i * 8 * $sin));
        $a = $glyph(0, 'A');
        $b = $glyph(1, 'B');
        $c = $glyph(2, 'C');

        static::assertSame([[$a, $b, $c]], BaselineClusterStrategy::group([$c, $a, $b]));
    }

    public function testReadsRotatedWordAlongItsBaselineDirection(): void {
        // The baseline points up the page, so the word reads bottom to top.
        $bottom = $this->element('W', new TransformationMatrix(0, 1, -1, 0, 300, 500));
        $middle = $this->element('O', new TransformationMatrix(0, 1, -1, 0, 300, 560));
        $top = $this->element('R', new TransformationMatrix(0, 1, -1, 0, 300, 620));

        static::assertSame([[$bottom, $middle, $top]], BaselineClusterStrategy::group([$top, $bottom, $middle]));
    }

    public function testKeepsParallelBaselinesAtDifferentPositionsSeparate(): void {
        $first = $this->element('A', new TransformationMatrix(0, -1, 1, 0, 300, 700));
        $second = $this->element('B', new TransformationMatrix(0, -1, 1, 0, 100, 700));

        static::assertSame([[$first], [$second]], BaselineClusterStrategy::group([$second, $first]));
    }

    public function testOrdersLinesOfABlockRotatedPastVerticalInReadingOrder(): void {
        // At 200 degrees the next line lies higher on the page, so a sort by page Y would reverse the block.
        $cos = cos(deg2rad(200));
        $sin = sin(deg2rad(200));
        $line = fn(int $k, string $text): PositionedTextElement => $this->element($text, new TransformationMatrix($cos, $sin, -$sin, $cos, 300 + $k * 15 * $sin, 400 - $k * 15 * $cos), 12);
        $first = $line(0, 'A');
        $second = $line(1, 'B');
        $third = $line(2, 'C');

        static::assertSame([[$first], [$second], [$third]], BaselineClusterStrategy::group([$third, $first, $second]));
    }

    public function testKeepsARotatedOverlayContiguousInsteadOfInterleavingWithHorizontalBody(): void {
        $bodyTop = $this->element('top', new TransformationMatrix(1, 0, 0, 1, 100, 700), 12);
        $bodyMiddle = $this->element('middle', new TransformationMatrix(1, 0, 0, 1, 100, 690), 12);
        $bodyBottom = $this->element('bottom', new TransformationMatrix(1, 0, 0, 1, 100, 680), 12);

        $cos = cos(deg2rad(45));
        $sin = sin(deg2rad(45));
        $overlayFirst = $this->element('O1', new TransformationMatrix($cos, $sin, -$sin, $cos, 200, 695), 12);
        $overlaySecond = $this->element('O2', new TransformationMatrix($cos, $sin, -$sin, $cos, 210, 685), 12);

        static::assertSame(
            [[$bodyTop], [$bodyMiddle], [$bodyBottom], [$overlayFirst], [$overlaySecond]],
            BaselineClusterStrategy::group([$overlaySecond, $bodyBottom, $overlayFirst, $bodyTop, $bodyMiddle]),
        );
    }

    public function testDetectsColumnsOfRotatedText(): void {
        // Two columns of text that runs up the page: lines are stacked from left to right, columns from bottom to top.
        $firstColumn = $secondColumn = [];
        for ($k = 0; $k < 30; $k++) {
            $firstColumn[] = $this->element(str_repeat('a', 40), new TransformationMatrix(0, 1, -1, 0, 100 + $k * 12, 100));
            $secondColumn[] = $this->element(str_repeat('b', 40), new TransformationMatrix(0, 1, -1, 0, 100 + $k * 12, 400));
        }

        static::assertSame(
            array_map(static fn(PositionedTextElement $e) => [$e], [...$firstColumn, ...$secondColumn]),
            BaselineClusterStrategy::group([...$secondColumn, ...$firstColumn]),
        );
    }

    /** A text element whose glyphs are each half an em wide */
    private function element(string $text, TransformationMatrix $matrix, float $fontSize = 10): PositionedTextElement {
        $font = self::createStub(Font::class);
        $font->method('getWidthForBytes')->willReturnCallback(static fn(array $bytes, float $wordSpace, float $charSpace, float $fontSize): float => count($bytes) * 0.5 * $fontSize);

        return new PositionedTextElement([new TextSegment(null, new LiteralStringValue($text))], $matrix, new TextState($font, $fontSize));
    }
}
