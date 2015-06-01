<?php
namespace Hostnet\FormTwigBridge;

/**
 * @covers Hostnet\FormTwigBridge\TwigEnvironmentBuilder
 */
class TwigEnvironmentBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testSetCsrfTokenManager()
    {
        // Test chaining
        $builder       = new TwigEnvironmentBuilder();
        $token_manager = $this->prophesize('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
        $this->assertSame($builder, $builder->setCsrfTokenManager($token_manager->reveal()));
    }

    public function testSetTranslator()
    {
        // Test chaining
        $builder = new TwigEnvironmentBuilder();
        $this->assertEquals(
            $builder,
            $builder->setTranslator($this->getMock('Symfony\Component\Translation\TranslatorInterface'))
        );
    }

    /**
     * @expectedException DomainException
     */
    public function testBuildWithoutCSRF()
    {
        $builder = new TwigEnvironmentBuilder();
        $builder->build();
    }

    /**
     * @expectedException DomainException
     */
    public function testBuildWithoutTranslatorBuilder()
    {
        $builder       = new TwigEnvironmentBuilder();
        $token_manager = $this->prophesize('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
        $builder->setCsrfTokenManager($token_manager->reveal());
        $builder->build();
    }

    public function testBuild()
    {
        $builder       = new TwigEnvironmentBuilder();
        $token_manager = $this->prophesize('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
        $builder->setCsrfTokenManager($token_manager->reveal());
        $builder->setTranslator($this->getMock('Symfony\Component\Translation\TranslatorInterface'));
        $this->assertInstanceOf('Twig_Environment', $builder->build());
    }
}
