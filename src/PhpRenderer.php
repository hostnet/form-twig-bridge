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
 * @author Nico Schoenmaker <nschoenmaker@hostnet.nl>
 */
class PhpRenderer
{
    /**
     * @var \Twig_Environment
     */
    private $environment;

    public function __construct(\Twig_Environment $environment)
    {
        if (!$environment->hasExtension('form')) {
            throw new \DomainException('The FormRenderer needs an environment with the FormExtension');
        }
        $this->environment = $environment;
        $this->environment->getExtension('form')->initRuntime($this->environment);
    }

    /**
     * Renders the opening form tag of the form
     * @param FormView $view
     * @param array $variables
     */
    public function renderStart(FormView $view, array $variables = [])
    {
        return $this->renderBlock($view, 'start', $variables);
    }

    /**
     * Renders the closing form tag of the form
     * @param FormView $view
     * @param array $variables
     */
    public function renderEnd(FormView $view, array $variables = [])
    {
        return $this->renderBlock($view, 'end', $variables);
    }

    /**
     * Renders the form widget
     * @param FormView $view
     * @param array $variables
     */
    public function renderWidget(FormView $view, array $variables = [])
    {
        return $this->renderBlock($view, 'widget', $variables);
    }

    /**
     * Renders only the errors of the passed FormView
     * @param FormView $view
     * @param array $variables
     */
    public function renderErrors(FormView $view, array $variables = [])
    {
        return $this->renderBlock($view, 'errors', $variables);
    }

    /**
     * Renders the label of a field
     * @param FormView $view
     * @param array $variables
     */
    public function renderLabel(FormView $view, array $variables = [])
    {
        return $this->renderBlock($view, 'label', $variables);
    }

    /**
     * Renders a row for a field
     * @param FormView $view
     * @param array $variables
     */
    public function renderRow(FormView $view, array $variables = [])
    {
        return $this->renderBlock($view, 'row', $variables);
    }

    /**
     * Renders all unrendered children of the given form
     * @param FormView $view
     * @param array $variables
     */
    public function renderRest(FormView $view, array $variables = [])
    {
        return $this->renderBlock($view, 'rest', $variables);
    }

    private function renderBlock(FormView $view, $block, array $variables = [])
    {
        return $this->environment->getExtension('form')->renderer
             ->searchAndRenderBlock($view, $block, $variables);
    }
}
