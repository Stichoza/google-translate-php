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

        $this->assertEqualsIgnoringCase('გამარჯობა', $result, 'Translation should be correct.');
    }

    public function testTranslationEquality(): void
    {
        $resultOne = GoogleTranslate::trans('Hello', 'ka', 'en');
        $resultTwo = $this->tr->setSource('en')->setTarget('ka')->translate('Hello');

        $this->assertEqualsIgnoringCase($resultOne, $resultTwo, 'Static and instance methods should return same result.');
    }

    public function testTranslationKeyExtraction(): void
    {
        $resultOne = GoogleTranslate::trans('Hello :name how are :type_of_greeting?', 'fr', 'en', preserveParameters: true);
        $resultTwo = $this->tr->setSource('en')->setTarget('fr')->preserveParameters()->translate('Hello :name, how are :type_of_greeting?');

        $this->assertEquals('Bonjour :name, comment va :type_of_greeting ?', $resultOne, 'Translation should be correct with proper key extraction.');
        $this->assertEquals('Bonjour :name, comment va :type_of_greeting ?', $resultTwo, 'Translation should be correct with proper key extraction.');
    }

    public function testCanIgnoreTranslationKeyExtraction(): void
    {
        $resultOne = GoogleTranslate::trans('Hello :name how are :greeting?', 'fr', 'en');
        $resultTwo = $this->tr->setSource('en')->setTarget('fr')->translate('Hello :name how are :greeting?');

        $this->assertEquals('Bonjour :nom, comment allez-vous :salut ?', $resultOne, 'Translation should be correct and ignores key extraction if not set.');
        $this->assertEquals('Bonjour :nom, comment allez-vous :salut ?', $resultTwo, 'Translation should be correct and ignores key extraction if not set.');
    }

    public function testCanCustomizeExtractionPattern(): void
    {
        $resultOne = GoogleTranslate::trans('Hello {{name}}, how are {{type_of_greeting}}?', 'fr', 'en', preserveParameters: '/\{\{([^}]+)\}\}/');
        $resultTwo = $this->tr->setSource('en')->setTarget('fr')->preserveParameters('/\{\{([^}]+)\}\}/')->translate('Hello {{name}}, how are {{type_of_greeting}}?');

        $this->assertEquals('Bonjour {{name}}, comment va {{type_of_greeting}} ?', $resultOne, 'Translation should be correct and ignores key extraction if not set.');
        $this->assertEquals('Bonjour {{name}}, comment va {{type_of_greeting}} ?', $resultTwo, 'Translation should be correct and ignores key extraction if not set.');
    }

    public function testNewerLanguageTranslation(): void
    {
        $result = $this->tr->setSource('en')->setTarget('tk')->translate('Hello');

        $this->assertEqualsIgnoringCase('Salam', $result, 'Newer languages should be translatable.');
    }

    public function testUTF16Translation(): void
    {
        $result = $this->tr->setSource('en')->setTarget('de')->translate('yes 👍🏽');

        $this->assertEqualsIgnoringCase('ja 👍🏽', $result, 'UTF-16 strings should be translatable');
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

    /**
     * @see https://github.com/Stichoza/google-translate-php/issues/201
     */
    public function testItProperlyTranslateStringsInFrenchThatWouldOtherwiseCauseIssues(): void
    {
        $resultOne = $this->tr->setSource('en')->setTarget('fr')->translate('What is :real_q_encoded?');
        $resultTwo = $this->tr->setSource('en')->setTarget('fr')->preserveParameters('#\{([^}]+)}#')->translate('What is {real_q_encoded}?');

        $this->assertEquals('Qu\'est-ce que :real_q_encoded ?', $resultOne, 'Translation should be correct.');
        $this->assertEquals('Qu\'est-ce que {real_q_encoded} ?', $resultTwo, 'Translation should be correct.');
    }
}
