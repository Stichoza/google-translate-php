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

    public function testArrayTranslation()
    {
        $data = [
            'This looks like',
            'an awesome array',
            'with some sentences'
        ];
        $source = 'en';
        $target = 'ka';

        $this->tr->setSource($source)->setTarget($target);

        $translatedData = [];
        foreach ($data as $key => $text) {
            $translatedData[$key] = $this->tr->translate($text);
        }

        foreach ($this->tr->translate($data) as $key => $text) {
            $this->assertEquals($translatedData[$key], $text);
        }

        // testing static
        foreach (TranslateClient::translate($source, $target, $data) as $key => $text) {
            $this->assertEquals($translatedData[$key], $text);
        }
    }

    public function testRawResponse()
    {
        $rawResult = $this->tr->getResponse('cat');

        $this->assertTrue(is_array($rawResult), 'Method getResponse() should return an array.');
    }
}
