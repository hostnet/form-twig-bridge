<?php
namespace Hostnet\FormTwigBridge;
use Symfony\Component\Form\FormExtensionInterface;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;

use Symfony\Component\Validator\Validation;

use Symfony\Component\Form\Extension\Validator\ValidatorExtension;

use Symfony\Component\Form\Extension\Csrf\CsrfExtension;

use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;

use Symfony\Component\Form\Forms;

use Symfony\Component\Form\FormFactoryBuilder;

/**
 * Uses the builder pattern to create a form factory and a Twig_Environment through the
 * TwigEnvironmentBuilder
 * @author nschoenmaker
 */
class Builder
{
  private $csrf_provider;

  private $annotation_mapping_enabled = false;

  private $form_extensions = array();

  /**
   * The CSRF secret the form framework should use
   * @param CsrfProviderInterface $csrf_provider
   * @return \Hostnet\FormTwigBridge\Builder
   */
  public function setCsrfProvider(CsrfProviderInterface $csrf_provider)
  {
    $this->csrf_provider = $csrf_provider;
    return $this;
  }

  /**
   * Creates a builder you can use to get the Twig_Environment
   * @return \Hostnet\FormTwigBridge\\Hostnet\FormTwigBridge\TwigEnvironmentBuilder
   */
  public function createTwigEnvironmentBuilder()
  {
    $this->ensureCsrfProviderExists();
    $builder = new TwigEnvironmentBuilder();
    return $builder->setCsrfProvider($this->csrf_provider);
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
    $this->ensureCsrfProviderExists();
    $validator = $this->buildValidator();
    $builder = Forms::createFormFactoryBuilder()->addExtension(new CsrfExtension($this->csrf_provider))
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
    if($this->annotation_mapping_enabled) {
      $builder->enableAnnotationMapping();
    }
    return $builder->getValidator();
  }

  private function ensureCsrfProviderExists()
  {
    if(!$this->csrf_provider instanceof CsrfProviderInterface) {
      throw new \DomainException('The FormTwigBridge builder needs a csrf secret to continue');
    }
  }
}
