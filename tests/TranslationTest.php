<?php

namespace Stichoza\GoogleTranslate\Tests;

use PHPUnit\Framework\TestCase;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslationTest extends TestCase
{
    public GoogleTranslate $tr;

    public function setUp(): void
    {
        $this->tr = new GoogleTranslate();
    }

    public function testTranslation(): void
    {
        $result = $this->tr->setSource('en')->setTarget('ka')->translate('Hello');

        $this->assertEqualsIgnoringCase($result, 'გამარჯობა', 'Translation should be correct.');
    }

    public function testTranslationEquality(): void
    {
        $resultOne = GoogleTranslate::trans('Hello', 'ka', 'en');
        $resultTwo = $this->tr->setSource('en')->setTarget('ka')->translate('Hello');

        $this->assertEqualsIgnoringCase($resultOne, $resultTwo, 'Static and instance methods should return same result.');
    }

    public function testTranslationKeyExtraction(): void
    {
        $result = $this->tr->setSource('en')->setTarget('fr')->translate('Hello :name');

        $this->assertEquals('Bonjour :name', $result, 'Translation should be correct with proper key extraction.');
    }

    public function testNewerLanguageTranslation(): void
    {
        $result = $this->tr->setSource('en')->setTarget('tk')->translate('Hello');

        $this->assertEqualsIgnoringCase($result, 'Salam', 'Newer languages should be translatable.');
    }

    public function testUTF16Translation(): void
    {
        $result = $this->tr->setSource('en')->setTarget('de')->translate('yes 👍🏽');

        $this->assertEqualsIgnoringCase($result, 'ja 👍🏽', 'UTF-16 strings should be translatable');
    }

    public function testLargeTextTranslation(): void
    {
        $text = "Google Translate is a multilingual neural machine translation service developed by Google to translate text, documents and websites from one language into another. It offers a website interface, a mobile app for Android and iOS, and an API that helps developers build browser extensions and software applications. As of November 2022, Google Translate supports 133 languages at various levels, and as of April 2016, claimed over 500 million total users, with more than 100 billion words translated daily, after the company stated in May 2013 that it served over 200 million people daily.";

        $output = $this->tr->setTarget('uk')->translate($text);

        $this->assertIsString($output, 'Translation should be string');
        $this->assertNotEmpty($output, 'Translation should not be empty');
        $this->assertNotEqualsIgnoringCase($text, $output, 'Translation should be different from original');
    }

    public function testRawResponse(): void
    {
        $rawResult = $this->tr->getResponse('cat');

        $this->assertIsArray($rawResult, 'Method getResponse() should return an array');
    }

    public function testGetReplacements(): void
    {
        $replacements = $this->tr->getReplacements('Hello :name are you :some_greeting?');

        $this->assertEquals(['name', 'some_greeting'], $replacements, 'Replacements should be extracted from string');
    }

    public function testGetEmptyReplacements(): void
    {
        $replacements = $this->tr->getReplacements('Hello');

        $this->assertEquals([], $replacements, 'Replacements should be empty');
    }

    public function testExtract(): void
    {
        $extracted = $this->tr->extract('Hello :name are you :some_greeting?');

        $this->assertEquals('Hello ${0} are you ${1}?', $extracted, 'Extraction should change strings to placeholder tokens');
    }

    public function testEmptyExtract(): void
    {
        $extracted = $this->tr->extract('Hello');

        $this->assertEquals('Hello', $extracted, 'Extraction should not change strings');
    }

    public function testInject(): void
    {
        $replaced = $this->tr->inject('Hello ${0} are you ${1}?', ['name', 'some_greeting']);

        $this->assertEquals('Hello :name are you :some_greeting?', $replaced, 'Replacement should change placeholder tokens to strings');
    }

    public function testEmptyInject(): void
    {
        $replaced = $this->tr->inject('Hello', []);

        $this->assertEquals('Hello', $replaced, 'Replacement should not change strings');
    }
}
