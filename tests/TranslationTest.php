<?php

namespace Stichoza\GoogleTranslate\Tests;

use PHPUnit\Framework\TestCase;
use Stichoza\GoogleTranslate\TranslateClient;

class TranslationTest extends TestCase
{
    public function setUp()
    {
        $this->tr = new TranslateClient();
    }

    public function testTranslationEquality()
    {
        $resultOne = TranslateClient::translate('en', 'ka', 'Hello');
        $resultTwo = $this->tr->setSource('en')->setTarget('ka')->translate('Hello');

        $this->assertEquals($resultOne, $resultTwo, 'გამარჯობა');
    }

    public function testUTF16Translation()
    {
        $resultOne = TranslateClient::translate('en', 'de', 'yes 👍🏽');
        $resultTwo = $this->tr->setSource('en')->setTarget('de')->translate('yes 👍🏽');

        $this->assertEquals($resultOne, $resultTwo, 'ja 👍🏽');
    }

    public function testRawResponse()
    {
        $rawResult = $this->tr->getResponse('cat');

        $this->assertTrue(is_array($rawResult), 'Method getResponse() should return an array.');
    }
}
