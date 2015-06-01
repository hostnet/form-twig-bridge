<?php
namespace Hostnet\FormTwigBridge;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validation;

/**
 * Uses the builder pattern to create a form factory and a Twig_Environment through the
 * TwigEnvironmentBuilder
 * @author nschoenmaker
 */
class Builder
{
  private $csrf_token_manager;

  private $translator;

  private $annotation_mapping_enabled = false;

  private $form_extensions = array();

  /**
   * The CSRF secret the form framework should use
   * @deprecated Use setCsrfTokenManager instead. To be removed when we support Symfony 3.0
   * @param CsrfProviderInterface $csrf_provider
   * @return \Hostnet\FormTwigBridge\Builder
   */
  public function setCsrfProvider(CsrfProviderInterface $csrf_provider)
  {
    trigger_error(
      'The CsrfProviderInterface is deprecated, use setCsrfTokenManager instead',
      E_USER_DEPRECATED
    );
    // We assign to the csrf_token_manager, since Symfony still accepts both at this time.
    $this->csrf_token_manager = $csrf_provider;
    return $this;
  }

  /**
   * The CSRF token manager to use
   *
   * @param CsrfTokenManagerInterface $csrf_token_manager
   * @return \Hostnet\FormTwigBridge\Builder
   */
  public function setCsrfTokenManager(CsrfTokenManagerInterface $csrf_token_manager)
  {
    $this->csrf_token_manager = $csrf_token_manager;
    return $this;
  }

  /**
   * The translator to use for translating messages.
   * You can use the TranslatorBuilder to create it
   * @param TranslatorInterface $translator
   * @return \Hostnet\FormTwigBridge\Builder
   */
  public function setTranslator(TranslatorInterface $translator)
  {
    $this->translator = $translator;
    return $this;
  }

  /**
   * Creates a builder you can use to get the Twig_Environment
   * @return \Hostnet\FormTwigBridge\\Hostnet\FormTwigBridge\TwigEnvironmentBuilder
   */
  public function createTwigEnvironmentBuilder()
  {
    $this->ensureCsrfTokenManagerAndTranslatorExist();
    $builder = new TwigEnvironmentBuilder();
    return $builder->setCsrfTokenManager($this->csrf_token_manager)->setTranslator($this->translator);
  }

  /**
   * Enable the annotation mapping. Only works if the doctrine/annotations library is included
   * @param bool $enabled Whether to enable the feature.
   * @return \Hostnet\FormTwigBridge\Builder
   */
  public function enableAnnotationMapping($enabled = true)
  {
    $this->annotation_mapping_enabled = (bool) $enabled;
    return $this;
  }

  /**
   * Add your own form extensions through this hook
   * @param FormExtensionInterface $extension
   * @return \Hostnet\FormTwigBridge\Builder
   */
  public function addFormExtension(FormExtensionInterface $extension)
  {
    $this->form_extensions[] = $extension;
    return $this;
  }

  /**
   * Builds the factory
   * @return \Symfony\Component\Form\FormFactoryInterface
   */
  public function buildFormFactory()
  {
    $this->ensureCsrfTokenManagerAndTranslatorExist();
    $validator = $this->buildValidator();
    $csrf = new CsrfExtension($this->csrf_token_manager, $this->translator, TranslatorBuilder::TRANSLATION_DOMAIN);
    $builder = Forms::createFormFactoryBuilder()->addExtension($csrf)
                                            ->addExtension(new ValidatorExtension($validator))
                                            ->addExtension(new HttpFoundationExtension());
    foreach($this->form_extensions as $extension) {
      $builder->addExtension($extension);
    }
    return $builder->getFormFactory();
  }

  private function buildValidator()
  {
    $builder = Validation::createValidatorBuilder();
    $builder->setTranslator($this->translator)->setTranslationDomain(TranslatorBuilder::TRANSLATION_DOMAIN);
    if($this->annotation_mapping_enabled) {
      $builder->enableAnnotationMapping();
    }
    return $builder->getValidator();
  }

  private function ensureCsrfTokenManagerAndTranslatorExist()
  {
    if(!$this->csrf_token_manager instanceof CsrfProviderInterface &&
      !$this->csrf_token_manager instanceof CsrfTokenManagerInterface) {
      throw new \DomainException('The FormTwigBridge builder needs a csrf secret to continue');
    }
    if(!$this->translator instanceof TranslatorInterface) {
      throw new \DomainException('The FormTwigBridge builder needs a translator to continue');
    }
  }
}
