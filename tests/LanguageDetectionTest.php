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
}
