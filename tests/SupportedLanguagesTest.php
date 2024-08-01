<?php

namespace Stichoza\GoogleTranslate\Tests;

use PHPUnit\Framework\TestCase;
use Stichoza\GoogleTranslate\GoogleTranslate;

class SupportedLanguagesTest extends TestCase
{
    public GoogleTranslate $tr;

    public function setUp(): void
    {
        $this->tr = new GoogleTranslate();
    }

    public function testLanguageCodesRequest(): void
    {
        $result = $this->tr->languages();
        $this->assertContains('en', $result);
        $this->assertContains('fr', $result);
        $this->assertContains('ka', $result);
        $this->assertContains('it', $result);
        $this->assertContains('pt', $result);
        $this->assertContains('pt-PT', $result);
        $this->assertContains('pl', $result);
        $this->assertContains('vi', $result);
        $this->assertContains('ja', $result);
        $this->assertContains('et', $result);
        $this->assertContains('hr', $result);
        $this->assertContains('es', $result);
        $this->assertContains('zh-CN', $result);
        $this->assertContains('zh-TW', $result);
    }

    public function testLocalizedLanguages(): void
    {
        $result = $this->tr->languages('en');
        $this->assertEquals('English', $result['en']);
        $this->assertEquals('French', $result['fr']);
        $this->assertEquals('Georgian', $result['ka']);
        $this->assertEquals('Italian', $result['it']);
        $this->assertEquals('Portuguese (Brazil)', $result['pt']);

        $result = $this->tr->languages('ka');
        $this->assertEquals('ინგლისური', $result['en']);
        $this->assertEquals('ფრანგული', $result['fr']);
        $this->assertEquals('ქართული', $result['ka']);
        $this->assertEquals('იტალიური', $result['it']);
        $this->assertEquals('პორტუგალიური (ბრაზილია)', $result['pt']);

        $result = $this->tr->languages('pt');
        $this->assertEquals('Inglês', $result['en']);
        $this->assertEquals('Francês', $result['fr']);
        $this->assertEquals('Georgiano', $result['ka']);
        $this->assertEquals('Italiano', $result['it']);
        $this->assertEquals('Português (Brasil)', $result['pt']);
    }

    public function testLanguagesEquality(): void
    {
        $resultOne = GoogleTranslate::langs();
        $resultTwo = $this->tr->languages();

        $this->assertEqualsIgnoringCase($resultOne, $resultTwo, 'Static and instance methods should return same result.');

        $resultOne = GoogleTranslate::langs('pt');
        $resultTwo = $this->tr->languages('pt');

        $this->assertEqualsIgnoringCase($resultOne, $resultTwo, 'Static and instance methods should return same result.');
    }
}
