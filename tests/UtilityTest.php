<?php
namespace Stichoza\GoogleTranslate\Tests;

use ReflectionClass;
use Stichoza\GoogleTranslate\TranslateClient;

class UtilityTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->tr = new TranslateClient();
        $reflection = new ReflectionClass(TranslateClient::class);
        $this->method = $reflection->getMethod('isValidLocale');
        $this->method->setAccessible(true);
    }

    public function testIsValidLocale()
    {
        $this->assertTrue($this->method->invokeArgs($this->tr, ['ab']));
        $this->assertTrue($this->method->invokeArgs($this->tr, ['ab-CD']));

        $this->assertFalse($this->method->invokeArgs($this->tr, ['ab-CDE']));
        $this->assertFalse($this->method->invokeArgs($this->tr, ['abc-DE']));
        $this->assertFalse($this->method->invokeArgs($this->tr, ['abc-DEF']));
        $this->assertFalse($this->method->invokeArgs($this->tr, ['abc']));
        $this->assertFalse($this->method->invokeArgs($this->tr, ['ab-']));
        $this->assertFalse($this->method->invokeArgs($this->tr, ['a']));
    }
}
