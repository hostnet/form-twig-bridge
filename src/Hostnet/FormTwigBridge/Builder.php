<?php
namespace Hostnet\FormTwigBridge;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;

use Symfony\Component\Form\FormFactoryBuilder;

use Symfony\Component\Validator\Validation;

use Symfony\Component\Form\Extension\Validator\ValidatorExtension;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;

use Symfony\Component\Form\Extension\Csrf\CsrfExtension;

use Symfony\Component\Form\Forms;

/**
 * Uses the builder pattern to create a form factory and a Twig_Environment through the
 * TwigEnvironmentBuilder
 * @author nschoenmaker
 */
class Builder
{
  private $csrf_provider;

  /**
   * The CSRF secret the form framework should use
   * @param String $csrf_secret
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
   * Builds the factory
   * @todo allow to add own form extensions?
   * @return \Symfony\Component\Form\FormFactoryInterface
   */
  public function buildFormFactory()
  {
    $this->ensureCsrfProviderExists();
    $validator = Validation::createValidator();
    return Forms::createFormFactoryBuilder()->addExtension(new CsrfExtension($this->csrf_provider))
                                            ->addExtension(new ValidatorExtension($validator))
                                            ->getFormFactory();
  }

  private function ensureCsrfProviderExists()
  {
    if(!$this->csrf_provider instanceof CsrfProviderInterface) {
      throw new \DomainException('The FormTwigBridge builder needs a csrf secret to continue');
    }
  }
}
