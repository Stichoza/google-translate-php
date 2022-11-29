<?php

namespace Stichoza\GoogleTranslate\Tests;

use PHPUnit\Framework\TestCase;
use Stichoza\GoogleTranslate\GoogleTranslate;

class LanguageDetectionTest extends TestCase
{
    public GoogleTranslate $tr;

    public function setUp(): void
    {
        $this->tr = new GoogleTranslate();
    }

    public function testSingleWord(): void
    {
        $this->tr->translate('გამარჯობა');
        $this->assertEquals($this->tr->getLastDetectedSource(), 'ka', 'Language detection should work for single words');

        $this->tr->translate('Cześć');
        $this->assertEquals($this->tr->getLastDetectedSource(), 'pl', 'Language detection should work for single words');
    }

    public function testSingleSentence(): void
    {
        $this->tr->translate('იყო არაბეთს როსტევან');
        $this->assertEquals($this->tr->getLastDetectedSource(), 'ka', 'Language detection should work for sentences');

        $this->tr->translate('Путин хуйло');
        $this->assertEquals($this->tr->getLastDetectedSource(), 'ru', 'Language detection should work for multiple sentences');
    }

    public function testMultipleSentence(): void
    {
        $this->tr->translate('ჩემი ხატია სამშობლო. სახატე - მთელი ქვეყანა. განათებული მთა-ბარი.');
        $this->assertEquals($this->tr->getLastDetectedSource(), 'ka', 'Language detection should work for multiple sentences');

        $this->tr->translate('Ще не вмерла Україна, И слава, и воля! Ще намъ, браття-молодці, Усміхнеться доля!');
        $this->assertEquals($this->tr->getLastDetectedSource(), 'uk', 'Language detection should work for multiple sentences');
    }
}
