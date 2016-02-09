<?php
namespace Hostnet\FormTwigBridge;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Maybe more of a functional-test, pokes around to find obvious failures in the composition
 * @covers Hostnet\FormTwigBridge\Builder
 * @author Nico Schoenmaker <nschoenmaker@hostnet.nl>
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testSetCsrfTokenManager()
    {
        // Test chaining
        $builder       = new Builder();
        $token_manager = $this->prophesize('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
        $this->assertSame($builder, $builder->setCsrfTokenManager($token_manager->reveal()));
    }

    public function testSetTranslator()
    {
        // Test chaining
        $builder    = new Builder();
        $translator = $this->prophesize('Symfony\Component\Translation\TranslatorInterface');
        $this->assertSame(
            $builder,
            $builder->setTranslator($translator->reveal())
        );
    }

    public function testEnableAnnotationMapping()
    {
        // Test chaining
        $builder = new Builder();
        $this->assertSame($builder, $builder->enableAnnotationMapping());
    }

    public function testAddFormExtension()
    {
        // Test chaining
        $builder   = new Builder();
        $extension = $this->prophesize('Symfony\Component\Form\FormExtensionInterface');
        $this->assertSame($builder, $builder->addFormExtension($extension->reveal()));
    }

    /**
     * @expectedException DomainException
     */
    public function testCreateTwigEnvironmentBuilderWithoutCSRF()
    {
        $builder = new Builder();
        $builder->createTwigEnvironmentBuilder();
    }

    /**
     * @expectedException DomainException
     */
    public function testCreateTwigEnvironmentBuilderWithoutTranslator()
    {
        $builder       = new Builder();
        $token_manager = $this->prophesize('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
        $builder->setCsrfTokenManager($token_manager->reveal())->createTwigEnvironmentBuilder();
    }

    public function testCreateTwigEnvironmentBuilder()
    {
        $builder       = new Builder();
        $token_manager = $this->prophesize('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
        $builder
            ->setCsrfTokenManager($token_manager->reveal())
            ->setTranslator($this->getMock('Symfony\Component\Translation\TranslatorInterface'));
        $this->assertInstanceOf(
            '\Hostnet\FormTwigBridge\TwigEnvironmentBuilder',
            $builder->createTwigEnvironmentBuilder()
        );
    }

    /**
     * @expectedException DomainException
     */
    public function testBuildFormFactoryWithoutCSRF()
    {
        $builder = new Builder();
        $builder->buildFormFactory();
    }

    /**
     * @expectedException DomainException
     */
    public function testBuildFormFactoryWithoutTranslator()
    {
        $builder       = new Builder();
        $token_manager = $this->prophesize('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
        $builder->setCsrfTokenManager($token_manager->reveal())->buildFormFactory();
    }

    public function testBuildFormFactory()
    {
        $builder       = new Builder();
        $token_manager = $this->prophesize('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
        $builder
            ->setCsrfTokenManager($token_manager->reveal())
            ->setTranslator($this->getMock('Symfony\Component\Translation\TranslatorInterface'));
        $this->assertInstanceOf(
            '\Symfony\Component\Form\FormFactoryInterface',
            $builder->buildFormFactory()
        );
    }

    public function testFunctionalTest()
    {
        $builder     = new Builder();
        $environment =
        $builder->setCsrfTokenManager($this->mockCsrf())->setTranslator($this->mockTranslator())->createTwigEnvironmentBuilder()
              ->prependTwigLoader($this->mockLoader())->build();
        $factory     = $builder->buildFormFactory();
        $form        = $factory->createBuilder()->add('first_name')->getForm();

        $this
        ->assertEquals(
            $this->getExpectedOutput(),
            $environment->render('index.html.twig', array('form' => $form->createView()))
        );
    }

    private function getExpectedOutput()
    {
        return <<<HTML
Hi.<div id="form"><div><label for="form_first_name" class="required">First name</label><input type="text" id="form_first_name" name="form[first_name]" required="required" /></div><input type="hidden" id="form__token" name="form[_token]" value="foo" /></div>
HTML;
    }

    public function testFunctionalValidationTranslationTest()
    {
        $builder     = new Builder();
        $environment =
        $builder->setCsrfTokenManager($this->mockCsrf())->setTranslator($this->mockTranslator())->createTwigEnvironmentBuilder()
        ->prependTwigLoader($this->mockLoader())->build();
        $factory     = $builder->buildFormFactory();
        $options     = array('constraints' => array(new NotBlank()));
        $form        = $factory->createBuilder()->add('naam', TextType::class, $options)->getForm();

        $form->submit(array('naam' => ''));
        $this
        ->assertEquals(
            $this->getExpectedTranslatedOutput(),
            $environment->render('index.html.twig', array('form' => $form->createView()))
        );
    }

    private function getExpectedTranslatedOutput()
    {
        return 'Hi.<div id="form"><ul><li>De CSRF-token is ongeldig. Probeer het formulier opnieuw te versturen.</li></ul><div><label for="form_naam" class="required">Naam</label><ul><li>Deze waarde mag niet leeg zijn.</li></ul><input type="text" id="form_naam" name="form[naam]" required="required" /></div><input type="hidden" id="form__token" name="form[_token]" value="foo" /></div>';
    }

    private function mockCsrf()
    {
        $token_manager = $this->prophesize('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
        $token         = new CsrfToken('form', 'foo');
        $token_manager->getToken('form')->willReturn($token);
        return $token_manager->reveal();
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
