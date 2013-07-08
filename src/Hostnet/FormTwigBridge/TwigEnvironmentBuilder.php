<?php
namespace Hostnet\FormTwigBridge;
use Symfony\Component\Translation\TranslatorInterface;

use Symfony\Bridge\Twig\Form\TwigRenderer;

use Symfony\Bridge\Twig\Extension\FormExtension;

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
  const TEMPLATE_DIR = '/Symfony/Bridge/Twig/Resources/views/Form/';

  /**
   * The CSRF secret the form framework should use
   * @var CsrfProviderInterface
   */
  private $csrf_provider;

  /**
   * @var \Symfony\Component\Translation\TranslatorInterface
   */
  private $translator;

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
    $fixer = new VendorDirectoryFixer();
    $dir = $fixer->getLocation('twig-bridge', self::TEMPLATE_DIR);
    $this->twig_loader = new \Twig_Loader_Filesystem(array($dir));
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

  public function setTranslator(TranslatorInterface $translator)
  {
    $this->translator = $translator;
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
    if(!$this->translator instanceof TranslatorInterface) {
      throw new \DomainException('Need a translator to continue');
    }
    $environment = new \Twig_Environment($this->twig_loader);
    $this->addTranslationExtension($environment);
    $this->addFormExtension($environment);
    return $environment;
  }

  /**
   * Adds translation extension
   * Use ->setLocale() to translate to a different language
   * @param \Twig_environment $environment
   */
  private function addTranslationExtension(\Twig_environment $environment)
  {
    $environment->addExtension(new TranslationExtension($this->translator));
  }

  private function addFormExtension(\Twig_environment $environment)
  {
    $engine = new TwigRendererEngine(array($this->form_theme));
    $engine->setEnvironment($environment);
    $environment->addExtension(new FormExtension(new TwigRenderer($engine, $this->csrf_provider)));
  }
}
