<?php
namespace Hostnet\FormTwigBridge;

use Symfony\Component\Form\FormView;

/**
 * Class you can use to render the Symfony form component in a PHP template
 * All methods just forward to methods of the Twig FormExtension, so they are identical
 *
 * It needs a twig environment with the form extension and access to twig templates
 *
 * Check out the Builder and the TwigEnvironmentBuilder for how to build those!
 * @author nschoenmaker
 */
class PHPRenderer
{
  /**
   * @var \Twig_Environment
   */
  private $environment;

  public function __construct(\Twig_Environment $environment)
  {
    if(!$environment->hasExtension('form')) {
      throw new \DomainException('The FormRenderer needs an environment with the FormExtension');
    }
    $this->environment = $environment;
    $this->environment->getExtension('form')->initRuntime($this->environment);
  }

  /**
   * Renders the enctype attribute of the form
   * @param array $variables
   */
  public function renderEnctype(FormView $view, array $variables = array())
  {
    return $this->renderBlock($view, 'enctype', $variables);
  }

  /**
   * Renders the form widget
   * @param FormView $view
   * @param array $variables
   */
  public function renderWidget(FormView $view, array $variables = array())
  {
    return $this->renderBlock($view, 'widget', $variables);
  }

  /**
   * Renders only the errors of the passed FormView
   * @param FormView $view
   * @param array $variables
   */
  public function renderErrors(FormView $view, array $variables = array())
  {
    return $this->renderBlock($view, 'errors', $variables);
  }

  /**
   * Renders the label of a field
   * @param FormView $view
   * @param array $variables
   */
  public function renderLabel(FormView $view, array $variables = array())
  {
    return $this->renderBlock($view, 'label', $variables);
  }

  /**
   * Renders a row for a field
   * @param FormView $view
   * @param array $variables
   */
  public function renderRow(FormView $view, array $variables = array())
  {
    return $this->renderBlock($view, 'row', $variables);
  }

  /**
   * Renders all unrendered children of the given form
   * @param FormView $view
   * @param array $variables
   */
  public function renderRest(FormView $view, array $variables = array())
  {
    return $this->renderBlock($view, 'rest', $variables);
  }

  private function renderBlock(FormView $view, $block, array $variables = array())
  {
    return $this->environment->getExtension('form')->renderer
                ->searchAndRenderBlock($view, $block, $variables);
  }
}
