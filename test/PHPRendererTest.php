<?php
namespace Hostnet\FormTwigBridge;

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @covers Hostnet\FormTwigBridge\PHPRenderer
 */
class PHPRendererTest extends \PHPUnit_Framework_TestCase
{
    private $csrf;

    private $translator;

    public function setUp()
    {
        $this->csrf = $this->prophesize('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
        $token      = new CsrfToken('form', 'foo');
        $this->csrf->getToken('form')->willReturn($token);
        $builder = new TranslatorBuilder();
        $builder->setLocale('nl_NL');
        $this->translator = $builder->build();
    }

    /**
     * @expectedException DomainException
     */
    public function testConstructWithoutFormExtension()
    {
        new PHPRenderer(new \Twig_Environment());
    }

    public function testRenderEnctype()
    {
        // Not a special form - empty result
        $environment = $this->mockEnvironment();
        $form_view   = $this->mockForm()->createView();
        $renderer    = new PHPRenderer($environment);
        $this->assertEquals('', $renderer->renderEnctype($form_view));

        // Lets test a file upload
        $builder  = new Builder();
        $factory  = $builder->setCsrfTokenManager($this->csrf->reveal())->setTranslator($this->translator)->buildFormFactory();
        $form     = $factory->createBuilder()->add('picture', 'file')->getForm();
        $renderer = new PHPRenderer($environment);
        $html     = 'enctype="multipart/form-data"';
        $this->assertEquals($html, $renderer->renderEnctype($form->createView()));
    }

    public function testRenderWidget()
    {
        $environment = $this->mockEnvironment();
        $form_view   = $this->mockForm()->createView();
        $renderer    = new PHPRenderer($environment);
        $html        =
        '<div id="form"><div>                <label for="form_naam" class="required">Naam</label><input type="text" id="form_naam" name="form[naam]" required="required" /></div><input type="hidden" id="form__token" name="form[_token]" value="foo" /></div>';
        $this->assertEquals($html, $renderer->renderWidget($form_view));
    }

    public function testRenderErrors()
    {
        // Unbound form - empty result
        $environment = $this->mockEnvironment();
        $form        = $this->mockForm();
        $renderer    = new PHPRenderer($environment);
        $this->assertEquals('', $renderer->renderErrors($form->createView()));

        // Lets bind it, give some errors
        $form->submit(array());
        $renderer = new PHPRenderer($environment);
        $html     = '<ul><li>De CSRF-token is ongeldig. Probeer het formulier opnieuw te versturen.</li></ul>';
        $this->assertEquals($html, $renderer->renderErrors($form->createView()));
    }

    public function testRenderLabel()
    {
        $environment = $this->mockEnvironment();
        $form_view   = $this->mockForm()->createView();
        $field       = $form_view->children['naam'];
        $renderer    = new PHPRenderer($environment);
        $html        = '                <label for="form_naam" class="required">Naam</label>';
        $this->assertEquals($html, $renderer->renderLabel($field));
    }

    public function testRenderRowAndRest()
    {
        $environment = $this->mockEnvironment();
        $form        = $this->mockForm()->createView();
        $renderer    = new PHPRenderer($environment);
        $html        =
        '<div>                <label for="form_naam" class="required">Naam</label><input type="text" id="form_naam" name="form[naam]" required="required" /></div>';
        $this->assertEquals($html, $renderer->renderRow($form->children['naam']));

      // good opportunity to test renderRest as well. Renders all the other fields
        $html = '<input type="hidden" id="form__token" name="form[_token]" value="foo" />';
        $this->assertEquals($html, $renderer->renderRest($form));
    }

    private function mockEnvironment()
    {
        $builder = new TwigEnvironmentBuilder();
        return $builder->setCsrfTokenManager($this->csrf->reveal())->setTranslator($this->translator)->build();
    }

    private function mockForm()
    {
        $builder = new Builder();
        $factory = $builder->setCsrfTokenManager($this->csrf->reveal())->setTranslator($this->translator)->buildFormFactory();
        $options = array('constraints' => array(new NotBlank()));
        return $factory->createBuilder()->add('naam', 'text', $options)->getForm();
    }
}
