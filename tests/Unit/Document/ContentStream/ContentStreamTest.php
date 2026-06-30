<?php declare(strict_types=1);

namespace PrinsFrank\PdfParser\Tests\Unit\Document\ContentStream;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PrinsFrank\PdfParser\Document\ContentStream\ContentStream;
use PrinsFrank\PdfParser\Document\ContentStream\ContentStreamParser;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\PositionedTextElement;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TextState;
use PrinsFrank\PdfParser\Document\ContentStream\PositionedText\TransformationMatrix;
use PrinsFrank\PdfParser\Document\Dictionary\Dictionary;
use PrinsFrank\PdfParser\Document\Dictionary\DictionaryEntry\DictionaryEntry;
use PrinsFrank\PdfParser\Document\Dictionary\DictionaryKey\DictionaryKey;
use PrinsFrank\PdfParser\Document\Dictionary\DictionaryKey\ExtendedDictionaryKey;
use PrinsFrank\PdfParser\Document\Dictionary\DictionaryValue\Reference\ReferenceValue;
use PrinsFrank\PdfParser\Document\Dictionary\ResourceDictionaryChain;
use PrinsFrank\PdfParser\Document\Document;
use PrinsFrank\PdfParser\Document\Object\Decorator\GenericObject;
use PrinsFrank\PdfParser\Document\Object\Decorator\XObject;
use PrinsFrank\PdfParser\Stream\FileStream;

#[CoversClass(ContentStream::class)]
class ContentStreamTest extends TestCase {
    public function testGetPositionedTextElements(): void {
        $contentStream = FileStream::fromString(<<<EOD
            1 0 0 -1 0 842 cm
            q
            .75 0 0 .75 0 0 cm
            1 1 1 RG 1 1 1 rg
            /G3 gs
            0 0 794 1123 re
            f
            Q
            q
            .75 0 0 .75 72 72 cm
            0 0 0 RG 0 0 0 rg
            /G3 gs
            /P <</MCID 0 >>BDC
            BT
            /F4 14.666667 Tf
            1 0 0 -1 0 .47981739 Tm
            0 -13.2773438 Td <0024> Tj
            9.7756042 0 Td <0025> Tj
            9.7756042 0 Td <0026> Tj
            ET
            BT
            /F4 14.666667 Tf
            1 0 0 -1 0 .47981739 Tm
            30.135483 -13.2773438 Td <0003> Tj
            ET
            Q
            q
            .75 0 0 .75 72 86.546265 cm
            0 0 0 RG 0 0 0 rg
            /G3 gs
            EMC
            /P <</MCID 1 >>BDC
            BT
            /F4 14.666667 Tf
            1 0 0 -1 0 .47981739 Tm
            0 -13.2773438 Td <0027> Tj
            10.5842743 0 Td <0028> Tj
            9.7756042 0 Td <0029> Tj
            ET
            BT
            /F4 14.666667 Tf
            1 0 0 -1 0 .47981739 Tm
            29.3125 -13.2773438 Td <0003> Tj
            ET
            Q
            q
            .75 0 0 .75 72 101.092529 cm
            0 0 0 RG 0 0 0 rg
            /G3 gs
            EMC
            /P <</MCID 2 >>BDC
            BT
            /Span<</ActualText <FEFF200B> >> BDC
            /F4 14.666667 Tf
            1 0 0 -1 0 .47981739 Tm
            0 -13.2773438 Td <0003> Tj
            EMC
            ET
            BT
            /Span<</ActualText <FEFF200B> >> BDC
            /F4 14.666667 Tf
            1 0 0 -1 0 .47981739 Tm
            48 -13.2773438 Td <0003> Tj
            EMC
            ET
            BT
            /F4 14.666667 Tf
            1 0 0 -1 0 .47981739 Tm
            96 -13.2773438 Td <002A> Tj
            11.4001007 0 Td <002B> Tj
            10.5842743 0 Td <002C> Tj
            ET
            BT
            /F4 14.666667 Tf
            1 0 0 -1 0 .47981739 Tm
            122.056351 -13.2773438 Td <0003> Tj
            4.0719757 0 Td <0003> Tj
            4.0719757 0 Td <0003> Tj
            4.0719757 0 Td <0003> Tj
            4.0719757 0 Td <0003> Tj
            4.0719757 0 Td <0003> Tj
            ET
            BT
            /F4 14.666667 Tf
            1 0 0 -1 0 .47981739 Tm
            146.488205 -13.2773438 Td <002D> Tj
            7.328125 0 Td <002E> Tj
            9.7756042 0 Td <002F> Tj
            ET
            BT
            /F4 14.666667 Tf
            1 0 0 -1 0 .47981739 Tm
            171.74304 -13.2773438 Td <0003> Tj
            ET
            Q
            q
            .75 0 0 .75 72 115.638794 cm
            0 0 0 RG 0 0 0 rg
            /G3 gs
            EMC
            /P <</MCID 3 >>BDC
            BT
            /F4 14.666667 Tf
            1 0 0 -1 0 .47981739 Tm
            0 -13.2773438 Td <0003> Tj
            ET
            Q
            EMC
        EOD);
        $decoratedObject = $this->createMock(GenericObject::class);
        $decoratedObject->expects(self::once())->method('getStream')->willReturn($contentStream);
        static::assertEquals(
            [
                new PositionedTextElement('<0024>', new TransformationMatrix(0.75, 0, 0, 0.75, 72.0, 759.6821291075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0025>', new TransformationMatrix(0.75, 0, 0, 0.75, 79.33170315, 759.6821291075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0026>', new TransformationMatrix(0.75, 0, 0, 0.75, 86.6634063, 759.6821291075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0003>', new TransformationMatrix(0.75, 0, 0, 0.75, 94.60161225, 759.6821291075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0027>', new TransformationMatrix(0.75, 0, 0, 0.75, 72.0, 745.1358641075001), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0028>', new TransformationMatrix(0.75, 0, 0, 0.75, 79.938205725, 745.1358641075001), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0029>', new TransformationMatrix(0.75, 0, 0, 0.75, 87.269908875, 745.1358641075001), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0003>', new TransformationMatrix(0.75, 0, 0, 0.75, 93.984375, 745.1358641075001), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0003>', new TransformationMatrix(0.75, 0, 0, 0.75, 72.0, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0003>', new TransformationMatrix(0.75, 0, 0, 0.75, 108.0, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<002A>', new TransformationMatrix(0.75, 0, 0, 0.75, 144.0, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<002B>', new TransformationMatrix(0.75, 0, 0, 0.75, 152.550075525, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<002C>', new TransformationMatrix(0.75, 0, 0, 0.75, 160.48828125, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0003>', new TransformationMatrix(0.75, 0, 0, 0.75, 163.54226325000002, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0003>', new TransformationMatrix(0.75, 0, 0, 0.75, 166.596245025, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0003>', new TransformationMatrix(0.75, 0, 0, 0.75, 169.65022679999998, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0003>', new TransformationMatrix(0.75, 0, 0, 0.75, 172.704208575, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0003>', new TransformationMatrix(0.75, 0, 0, 0.75, 175.75819035, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0003>', new TransformationMatrix(0.75, 0, 0, 0.75, 178.812172125, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<002D>', new TransformationMatrix(0.75, 0, 0, 0.75, 181.86615375, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<002E>', new TransformationMatrix(0.75, 0, 0, 0.75, 187.3622475, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<002F>', new TransformationMatrix(0.75, 0, 0, 0.75, 194.69395065, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0003>', new TransformationMatrix(0.75, 0, 0, 0.75, 200.80728, 730.5896001075), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
                new PositionedTextElement('<0003>', new TransformationMatrix(0.75, 0, 0, 0.75, 72.0, 716.0433351075001), new TextState(new ExtendedDictionaryKey('F4'), 14.666667)),
            ],
            ContentStreamParser::parse([$decoratedObject])->getPositionedTextElements(),
        );
    }

    public function testGetPositionedTextElementsWithTextStateOutsideTextObject(): void {
        $contentStream = FileStream::fromString(
            <<<EOD
            0 J
            /F1 7 Tf
            BT
            ([Hello) Tj
            (World]) Tj
            ET
            EOD,
        );
        $decoratedObject = $this->createMock(GenericObject::class);
        $decoratedObject->expects(self::once())->method('getStream')->willReturn($contentStream);
        static::assertEquals(
            [
                new PositionedTextElement('([Hello)', new TransformationMatrix(1.0, 0, 0, 1.0, 0.0, 0.0), new TextState(new ExtendedDictionaryKey('F1'), 7)),
                new PositionedTextElement('(World])', new TransformationMatrix(1.0, 0, 0, 1.0, 0.0, 0.0), new TextState(new ExtendedDictionaryKey('F1'), 7)),
            ],
            ContentStreamParser::parse([$decoratedObject])->getPositionedTextElements(),
        );
    }

    public function testGetPositionedTextElementsResolvesTextInFormXObject(): void {
        $pageContentStream = FileStream::fromString('/Fm1 Do');
        $pageObject = $this->createMock(GenericObject::class);
        $pageObject->expects(self::once())->method('getStream')->willReturn($pageContentStream);

        $formResources = new Dictionary(
            new DictionaryEntry(DictionaryKey::FONT, new Dictionary(
                new DictionaryEntry(new ExtendedDictionaryKey('F4'), new ReferenceValue(6, 0)),
            )),
        );
        $formObject = $this->createMock(XObject::class);
        $formObject->method('isForm')->willReturn(true);
        $formObject->method('getDictionary')->willReturn(new Dictionary(
            new DictionaryEntry(DictionaryKey::RESOURCES, $formResources),
        ));
        $formObject->method('getStream')->willReturn(FileStream::fromString(<<<EOD
            BT
            /F4 12 Tf
            1 0 0 1 10 20 Tm
            (Hi) Tj
            ET
            EOD));

        $document = $this->createMock(Document::class);
        $document->method('getObject')->willReturn($formObject);

        $pageResources = new Dictionary(
            new DictionaryEntry(DictionaryKey::XOBJECT, new Dictionary(
                new DictionaryEntry(new ExtendedDictionaryKey('Fm1'), new ReferenceValue(5, 0)),
            )),
        );

        // The form's own /Resources is prepended onto the page's, so the stamped chain is [form, page].
        static::assertEquals(
            [
                new PositionedTextElement('(Hi)', new TransformationMatrix(1, 0, 0, 1, 10.0, 20.0), new TextState(new ExtendedDictionaryKey('F4'), 12, resourceChain: new ResourceDictionaryChain([$formResources, $pageResources]))),
            ],
            ContentStreamParser::parse([$pageObject])->getPositionedTextElements($document, new ResourceDictionaryChain([$pageResources])),
        );
    }

    public function testGetPositionedTextElementsResolvesFormFontFromInheritedChain(): void {
        $pageContentStream = FileStream::fromString('/Fm1 Do');
        $pageObject = $this->createMock(GenericObject::class);
        $pageObject->expects(self::once())->method('getStream')->willReturn($pageContentStream);

        // The form has no /Resources of its own, so the font name it shows (/F4) can only be resolved by falling back
        // up the chain to the page's resources - the same chain that resolves the form (/Fm1) itself.
        $formObject = $this->createMock(XObject::class);
        $formObject->method('isForm')->willReturn(true);
        $formObject->method('getDictionary')->willReturn(new Dictionary());
        $formObject->method('getStream')->willReturn(FileStream::fromString(<<<EOD
            BT
            /F4 12 Tf
            1 0 0 1 10 20 Tm
            (Hi) Tj
            ET
            EOD));

        $document = $this->createMock(Document::class);
        $document->method('getObject')->willReturn($formObject);

        // One /Resources dictionary carries both the /XObject (to find the form) and the /Font the form inherits.
        $pageResources = new Dictionary(
            new DictionaryEntry(DictionaryKey::XOBJECT, new Dictionary(
                new DictionaryEntry(new ExtendedDictionaryKey('Fm1'), new ReferenceValue(5, 0)),
            )),
            new DictionaryEntry(DictionaryKey::FONT, new Dictionary(
                new DictionaryEntry(new ExtendedDictionaryKey('F4'), new ReferenceValue(6, 0)),
            )),
        );

        static::assertEquals(
            [
                new PositionedTextElement('(Hi)', new TransformationMatrix(1, 0, 0, 1, 10.0, 20.0), new TextState(new ExtendedDictionaryKey('F4'), 12, resourceChain: new ResourceDictionaryChain([$pageResources]))),
            ],
            ContentStreamParser::parse([$pageObject])->getPositionedTextElements($document, new ResourceDictionaryChain([$pageResources])),
        );
    }

    public function testGetPositionedTextElementsIgnoresNonFormXObject(): void {
        $pageContentStream = FileStream::fromString('/Im1 Do');
        $pageObject = $this->createMock(GenericObject::class);
        $pageObject->expects(self::once())->method('getStream')->willReturn($pageContentStream);

        $imageObject = $this->createMock(XObject::class);
        $imageObject->method('isForm')->willReturn(false);

        $document = $this->createMock(Document::class);
        $document->method('getObject')->willReturn($imageObject);

        $pageResources = new Dictionary(
            new DictionaryEntry(DictionaryKey::XOBJECT, new Dictionary(
                new DictionaryEntry(new ExtendedDictionaryKey('Im1'), new ReferenceValue(5, 0)),
            )),
        );

        static::assertSame(
            [],
            ContentStreamParser::parse([$pageObject])->getPositionedTextElements($document, new ResourceDictionaryChain([$pageResources])),
        );
    }
}
