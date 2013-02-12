<?php
namespace Hostnet\FormTwigBridge;
use Symfony\Bridge\Twig\Form\TwigRenderer;

use Symfony\Bridge\Twig\Extension\FormExtension;

use Symfony\Component\Form\Extension\Validator\ValidatorExtension;

use Symfony\Bridge\Twig\Extension\TranslationExtension;

use Symfony\Component\Translation\Loader\XliffFileLoader;

use Symfony\Component\Translation\Translator;

use Symfony\Bridge\Twig\Form\TwigRendererEngine;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;

/**
 * Responsible for building a Twig_environment
 * You need to pass in a csrf provider
 * It will (try to ;-) ) deduce the location of the composer vendor/ directory
 * @author nschoenmaker
 */
class TwigEnvironmentBuilder
{
  const TWIG_TEMPLATE_DIR = '/symfony/twig-bridge/Symfony/Bridge/Twig/Resources/views/Form/';
  const FORM_TRANSLATIONS_DIR = '/symfony/form/Symfony/Component/Form/Resources/translations/';
  const VALIDATOR_TRANSLATIONS_DIR = '/symfony/validator/Symfony/Component/Validator/Resources/translations/';

  /**
   * The location of the vendor directory
   * @var String
   */
  private $vendor_directory;

  /**
   * The CSRF secret the form framework should use
   * @var CsrfProviderInterface
   */
  private $csrf_provider;

  /**
   * If you want to add an additional loader
   * @var Twig_LoaderInterface
   */
  private $twig_loader;

  /**
   * The name of the root twig template
   * @var String
   */
  private $form_theme = 'form_div_layout.html.twig';

  public function __construct()
  {
    // Try the composed path
    $this->vendor_directory = __DIR__ . '/../../../../../../vendor/';
    try {
      $this->twig_loader =
        new \Twig_Loader_Filesystem(array($this->vendor_directory . self::TWIG_TEMPLATE_DIR));
    } catch(\Twig_Error_Loader $e) {
      // Fall back to the directly cloned path
      $this->vendor_directory = __DIR__ . '/../../../vendor/';
      $this->twig_loader =
        new \Twig_Loader_Filesystem(array($this->vendor_directory . self::TWIG_TEMPLATE_DIR));
    }
  }

  /**
   * The CSRF secret the form framework should use
   * @param CsrfProviderInterface $csrf_provider
   * @return \Hostnet\FormTwigBridge\TwigEnvironmentBuilder chainable
   */
  public function setCsrfProvider(CsrfProviderInterface $csrf_provider)
  {
    $this->csrf_provider = $csrf_provider;
    return $this;
  }

  /**
   * (Optionally) prepend a loader to override some of the default twig templates
   * @param \Twig_LoaderInterface $twig_loader
   * @return \Hostnet\FormTwigBridge\TwigEnvironmentBuilder chainable
   */
  public function prependTwigLoader(\Twig_LoaderInterface $twig_loader)
  {
    // Give precedence to the passed in loader
    $this->twig_loader = new \Twig_Loader_Chain(array($twig_loader, $this->twig_loader));
    return $this;
  }

  /**
   * Set the form theme
   * @param String $form_theme
   * @return \Hostnet\FormTwigBridge\TwigEnvironmentBuilder chainable
   */
  public function setFormTheme($form_theme)
  {
    $this->form_theme = $form_theme;
    return $this;
  }

  public function build()
  {
    if(!$this->csrf_provider instanceof CsrfProviderInterface) {
      throw new \DomainException('Need a csrf provider to continue');
    }
    $environment = new \Twig_Environment($this->twig_loader);
    $this->addTranslationExtension($environment);
    $this->addFormExtension($environment);
    return $environment;
  }

  /**
   * Adds translation extension
   * @todo Add support for other cultures?
   * @param \Twig_environment $environment
   */
  private function addTranslationExtension(\Twig_environment $environment)
  {
    // Set up the Translation component
    $translator = new Translator('en');
    $translator->addLoader('xlf', new XliffFileLoader());
    $translator
        ->addResource('xlf', $this->vendor_directory . self::FORM_TRANSLATIONS_DIR . 'validators.en.xlf', 'en', 'validators');
    $translator
        ->addResource('xlf', $this->vendor_directory . self::VALIDATOR_TRANSLATIONS_DIR . 'validators.en.xlf', 'en',
          'validators');
    $environment->addExtension(new TranslationExtension($translator));
  }

  private function addFormExtension(\Twig_environment $environment)
  {
    $engine = new TwigRendererEngine(array($this->form_theme));
    $engine->setEnvironment($environment);
    $environment->addExtension(new FormExtension(new TwigRenderer($engine, $this->csrf_provider)));
  }
}
