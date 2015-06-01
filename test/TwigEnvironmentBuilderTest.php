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
    $builder = new TwigEnvironmentBuilder();
    $token_manager = $this->prophesize('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
    $this->assertSame($builder, $builder->setCsrfTokenManager($token_manager->reveal()));
  }

  public function testSetTranslator()
  {
    // Test chaining
    $builder = new TwigEnvironmentBuilder();
    $this->assertEquals($builder,
        $builder->setTranslator($this->getMock('Symfony\Component\Translation\TranslatorInterface')));
  }

  public function testBuild()
  {
    // 1. Fail without CSRF
    $builder = new TwigEnvironmentBuilder();
    $this->assertDomainExceptionAtBuild($builder, 'Missing csrf secret');

    // 2. Fail without TranslatorBuilder
    $builder = new TwigEnvironmentBuilder();
    $token_manager = $this->prophesize('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
    $builder->setCsrfTokenManager($token_manager->reveal());
    $this->assertDomainExceptionAtBuild($builder, 'Missing translator');

    // 2. Gives back a Twig_Environment at success
    $builder = new TwigEnvironmentBuilder();
    $builder->setCsrfTokenManager($token_manager->reveal());
    $builder->setTranslator($this->getMock('Symfony\Component\Translation\TranslatorInterface'));
    $this->assertInstanceOf('Twig_Environment', $builder->build());
  }

  private function assertDomainExceptionAtBuild(TwigEnvironmentBuilder $builder, $message)
  {
    try {
      $builder->build();
      $this->fail($message);
    } catch(\DomainException $e) {
    }
  }
}
