<?php

namespace Stichoza\GoogleTranslate\Tests;

use PHPUnit\Framework\TestCase;
use Stichoza\GoogleTranslate\GoogleTranslate;

class LanguageDetectionTest extends TestCase
{
    public $tr;

    public function setUp()
    {
        $this->tr = new GoogleTranslate();
    }

    public function testSingleWord()
    {
        $this->tr->translate('გამარჯობა');
        $this->assertEquals($this->tr->getLastDetectedSource(), 'ka');

        $this->tr->translate('Cześć');
        $this->assertEquals($this->tr->getLastDetectedSource(), 'pl');
    }

    public function testSingleSentence()
    {
        $this->tr->translate('იყო არაბეთს როსტევან');
        $this->assertEquals($this->tr->getLastDetectedSource(), 'ka');

        $this->tr->translate('Путин хуйло');
        $this->assertEquals($this->tr->getLastDetectedSource(), 'ru');
    }

    public function testMultipleSentence()
    {
        $this->tr->translate('ჩემი ხატია სამშობლო. სახატე - მთელი ქვეყანა. განათებული მთა-ბარი.');
        $this->assertEquals($this->tr->getLastDetectedSource(), 'ka');

        $this->tr->translate('Ще не вмерла Україна, И слава, и воля! Ще намъ, браття-молодці, Усміхнеться доля!');
        $this->assertEquals($this->tr->getLastDetectedSource(), 'uk');
    }
}
