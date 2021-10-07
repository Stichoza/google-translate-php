<?php

namespace Stichoza\GoogleTranslate\Tests;

use PHPUnit\Framework\TestCase;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslationTest extends TestCase
{
    public $tr;

    public function setUp()
    {
        $this->tr = new GoogleTranslate();
    }

    public function testTranslationEquality()
    {
        try {
            $resultOne = GoogleTranslate::trans('Hello', 'ka', 'en');
        } catch (\ErrorException $e) {
            $resultOne = null;
        }
        $resultTwo = $this->tr->setSource('en')->setTarget('ka')->translate('Hello');

        $this->assertEquals($resultOne, $resultTwo, 'გამარჯობა');
    }

    public function testUTF16Translation()
    {
        try {
            $resultOne = GoogleTranslate::trans('yes 👍🏽', 'de', 'en');
        } catch (\ErrorException $e) {
            $resultOne = null;
        }
        $resultTwo = $this->tr->setSource('en')->setTarget('de')->translate('yes 👍🏽');

        $this->assertEquals($resultOne, $resultTwo, 'ja 👍🏽');
    }

    public function testRawResponse()
    {
        $rawResult = $this->tr->getResponse('cat');

        $this->assertIsArray($rawResult, 'Method getResponse() should return an array');
    }
}
