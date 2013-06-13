<?php
namespace Hostnet\FormTwigBridge\Tests;
use Hostnet\FormTwigBridge\TranslatorBuilder;

use Symfony\Component\Validator\Constraints\NotBlank;

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

  public function testSetTranslator()
  {
    // Test chaining
    $builder = new Builder();
    $this->assertEquals($builder,
        $builder->setTranslator($this->getMock('Symfony\Component\Translation\TranslatorInterface')));
  }

  public function testEnableAnnotationMapping()
  {
    // Test chaining
    $builder = new Builder();
    $this->assertEquals($builder, $builder->enableAnnotationMapping());
  }

  public function testAddFormExtension()
  {
    // Test chaining
    $extension = $this->getMock('Symfony\Component\Form\FormExtensionInterface', get_class_methods('Symfony\Component\Form\FormExtensionInterface'));
    $builder = new Builder();
    $this->assertEquals($builder, $builder->addFormExtension($extension));
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

    // 2. Try without translator
    $builder = new Builder();
    $builder->setCsrfProvider(new DefaultCsrfProvider('test'));
    try {
      $builder->createTwigEnvironmentBuilder();
      $this->fail('Should have thrown exception due to missing CSRF');
    } catch(\DomainException $e) {
    }

    // 3. Succes!
    $builder = new Builder();
    $builder->setCsrfProvider(new DefaultCsrfProvider('test'))
      ->setTranslator($this->getMock('Symfony\Component\Translation\TranslatorInterface'));
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

    // 2. Try without translator
    $builder = new Builder();
    $builder->setCsrfProvider(new DefaultCsrfProvider('test'));
    try {
      $builder->buildFormFactory();
      $this->fail('Should have thrown exception due to missing CSRF');
    } catch(\DomainException $e) {
    }

    // 2. Yay!
    $builder = new Builder();
    $builder->setCsrfProvider(new DefaultCsrfProvider('foo'))
      ->setTranslator($this->getMock('Symfony\Component\Translation\TranslatorInterface'));
    $this
        ->assertInstanceOf('\Symfony\Component\Form\FormFactoryInterface',
          $builder->buildFormFactory());
  }

  public function testFunctionalTest()
  {
    $builder = new Builder();
    $environment =
      $builder->setCsrfProvider($this->mockCsrf())->setTranslator($this->mockTranslator())->createTwigEnvironmentBuilder()
              ->prependTwigLoader($this->mockLoader())->build();
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

  public function testFunctionalValidationTranslationTest()
  {
    $builder = new Builder();
    $environment =
      $builder->setCsrfProvider($this->mockCsrf())->setTranslator($this->mockTranslator())->createTwigEnvironmentBuilder()
        ->prependTwigLoader($this->mockLoader())->build();
    $factory = $builder->buildFormFactory();
    $options = array('constraints' => array(new NotBlank()));
    $form = $factory->createBuilder()->add('naam', 'text', $options)->getForm();

    $form->bind(array('naam' => ''));
    $this
        ->assertEquals($this->getExpectedTranslatedOutput(),
          $environment->render('index.html.twig', array('form' => $form->createView())));
  }

  private function getExpectedTranslatedOutput()
  {
    return 'Hi.<div id="form"><ul><li>De CSRF-token is ongeldig. Probeer het formulier opnieuw te versturen.</li></ul><div><label for="form_naam" class="required">Naam</label><ul><li>Deze waarde mag niet leeg zijn.</li></ul><input type="text" id="form_naam" name="form[naam]" required="required" /></div><input type="hidden" id="form__token" name="form[_token]" value="foo" /></div>';
  }

  private function mockCsrf()
  {
    $csrf =
      $this
          ->getMock('Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface',
            array('generateCsrfToken', 'isCsrfTokenValid'));

    $csrf->expects($this->once())->method('generateCsrfToken')->will($this->returnValue('foo'));
    return $csrf;
  }

  private function mockTranslator()
  {
    $builder = new TranslatorBuilder();
    return $builder->setLocale('nl_NL')->build();
  }

  private function mockLoader()
  {
    return new \Twig_Loader_Array(array('index.html.twig' => 'Hi.{{ form_widget(form) }}'));
  }

}
