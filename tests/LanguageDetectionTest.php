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
        $this->tr->translate('იყო არაბეთს როსტევან. მეფე ღვთისაგან სვიანი. უხვლაშქრიანი და ისეთი რა');
        $this->assertEquals($this->tr->getLastDetectedSource(), 'ka');
    }
}
