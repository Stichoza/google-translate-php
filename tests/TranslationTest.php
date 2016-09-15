<?php

namespace Stichoza\GoogleTranslate\Tests;

use Stichoza\GoogleTranslate\TranslateClient;

class TranslationTest extends \PHPUnit_Framework_TestCase
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

    public function noTestArrayTranslation()
    {
        $this->tr->setSource('en')->setTarget('pt');

        $resultCat = $this->tr->translate('cat');
        $resultDog = $this->tr->translate('dog');
        $resultFish = $this->tr->translate('fish');

        $arrayResults = $this->tr->translate(['cat', 'dog', 'fish']);
        $arrayZesults = TranslateClient::translate('en', 'pt', ['cat', 'dog', 'fish']);

        $this->assertEquals($resultCat, $arrayResults[0], 'gato');
        $this->assertEquals($resultDog, $arrayResults[1], 'cachorro');
        $this->assertEquals($resultFish, $arrayResults[2], 'peixe');

        $this->assertEquals($resultCat, $arrayZesults[0], 'gato');
        $this->assertEquals($resultDog, $arrayZesults[1], 'cachorro');
        $this->assertEquals($resultFish, $arrayZesults[2], 'peixe');
    }

    public function testRawResponse()
    {
        $rawResult = $this->tr->getResponse('cat');

        $this->assertTrue(is_array($rawResult), 'Method getResponse() should return an array.');
    }
}
