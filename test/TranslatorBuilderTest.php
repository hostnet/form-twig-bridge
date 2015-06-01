<?php
namespace Hostnet\FormTwigBridge;

/**
 * @covers Hostnet\FormTwigBridge\TranslatorBuilder
 */
class TranslatorBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testSetLocale()
    {
        $builder = new TranslatorBuilder();
        $this->assertEquals($builder, $builder->setLocale('fo'));
    }

    public function testBuild()
    {
        $builder    = new TranslatorBuilder();
        $translator = $builder->setLocale('nl_NL')->build();
        $this->assertInstanceOf('Symfony\Component\Translation\TranslatorInterface', $translator);
        $this->assertEquals('Ongeldig creditcardnummer.', $translator->trans('Invalid card number.', array(), TranslatorBuilder::TRANSLATION_DOMAIN));
    }
}
