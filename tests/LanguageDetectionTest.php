<?php
namespace Stichoza\GoogleTranslate\Tests;

use Stichoza\GoogleTranslate\TranslateClient;

class LanguageDetectionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->tr = new TranslateClient();
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

    public function testStaticAndNonstaticDetection()
    {
        $this->tr->translate('გამარჯობა');
        
        TranslateClient::translate('Cześć');

        $this->assertNotEquals($this->tr->getLastDetectedSource(), 'ka');
        $this->assertNotEquals(TranslateClient::getLastDetectedSource(), 'ka');

        $this->assertEquals($this->tr->getLastDetectedSource(), 'uk');
        $this->assertEquals(TranslateClient::getLastDetectedSource(), 'uk');

        $this->tr->translate('გამარჯობა');

        $this->assertNotEquals($this->tr->getLastDetectedSource(), 'uk');
        $this->assertNotEquals(TranslateClient::getLastDetectedSource(), 'uk');
        $this->assertEquals($this->tr->getLastDetectedSource(), 'ka');
        $this->assertEquals(TranslateClient::getLastDetectedSource(), 'ka');
    }
}
