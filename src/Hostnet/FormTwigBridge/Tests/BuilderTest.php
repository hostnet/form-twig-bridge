<?php
namespace Hostnet\FormTwigBridge\Tests;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;

use Hostnet\FormTwigBridge\Builder;

/**
 * Maybe more of a functional-test, pokes around to find obvious failures in the composition
 * @author nschoenmaker
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{
  public function testSetCsrfProvider()
  {
    // Test chaining
    $builder = new Builder();
    $this->assertEquals($builder, $builder->setCsrfProvider(new DefaultCsrfProvider('bar')));
  }

  public function testCreateTwigEnvironmentBuilder()
  {
    // 1. Fail without CSRF
    $builder = new Builder();
    try {
      $builder->createTwigEnvironmentBuilder();
      $this->fail('Should have thrown exception due to missing CSRF');
    } catch(\DomainException $e) {
    }

    // 2. Succes!
    $builder = new Builder();
    $builder->setCsrfProvider(new DefaultCsrfProvider('test'));
    $this
        ->assertInstanceOf('\Hostnet\FormTwigBridge\TwigEnvironmentBuilder',
          $builder->createTwigEnvironmentBuilder());
  }

  public function testBuildFormFactory()
  {
    // 1. Fail without CSRF
    $builder = new Builder();
    try {
      $builder->buildFormFactory();
      $this->fail('Should have thrown exception due to missing CSRF');
    } catch(\DomainException $e) {
    }

    // 2. Yay!
    $builder = new Builder();
    $builder->setCsrfProvider(new DefaultCsrfProvider('foo'));
    $this
        ->assertInstanceOf('\Symfony\Component\Form\FormFactoryInterface',
          $builder->buildFormFactory());
  }

  public function testFunctionalTest()
  {
    $builder = new Builder();
    $loader = new \Twig_Loader_Array(array('index.html.twig' => 'Hi.{{ form_widget(form) }}'));

    $csrf =
      $this
          ->getMock('Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface',
            array('generateCsrfToken', 'isCsrfTokenValid'));

    $csrf->expects($this->once())->method('generateCsrfToken')->will($this->returnValue('foo'));

    $environment =
      $builder->setCsrfProvider($csrf)->createTwigEnvironmentBuilder()->prependTwigLoader($loader)
              ->build();
    $factory = $builder->buildFormFactory();
    $form = $factory->createBuilder()->add('first_name')->getForm();

    $this
        ->assertEquals($this->getExpectedOutput(),
          $environment->render('index.html.twig', array('form' => $form->createView())));
  }

  private function getExpectedOutput()
  {
    return <<<HTML
Hi.<div id="form"><div><label for="form_first_name" class="required">First name</label><input type="text" id="form_first_name" name="form[first_name]" required="required" /></div><input type="hidden" id="form__token" name="form[_token]" value="foo" /></div>
HTML;
  }
}
