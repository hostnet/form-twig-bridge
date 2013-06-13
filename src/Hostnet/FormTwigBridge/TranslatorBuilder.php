<?php
namespace Hostnet\FormTwigBridge;

use Symfony\Component\Translation\Loader\XliffFileLoader;

use Symfony\Component\Translation\Translator;

/**
 * Builds a TranslatorInterface. Adding the neccecary resources for the translation
 * @author nschoenmaker
 */
class TranslatorBuilder
{
  const TRANSLATION_DOMAIN = 'validators';

  const FORM_TRANSLATIONS_DIR = '/symfony/form/Symfony/Component/Form/Resources/translations/';
  const VALIDATOR_TRANSLATIONS_DIR = '/symfony/validator/Symfony/Component/Validator/Resources/translations/';

  private $locale;

  /**
   * @param String $locale The locale, like en, fr_FR, fr_BE
   * @return \Hostnet\FormTwigBridge\TranslatorBuilder
   */
  public function setLocale($locale)
  {
    $this->locale = $locale;
    return $this;
  }

  /**
   * Builds a translator
   * @return \Symfony\Component\Translation\TranslatorInterface
   */
  public function build()
  {
    $fixer = new VendorDirectoryFixer();
    $vendor_directory = $fixer->getVendorDirectory();
    // Set up the Translation component
    $translator = new Translator($this->locale);
    $pos = strpos($this->locale, '_');
    $file = 'validators.' . ($pos ? substr($this->locale, 0, $pos) : $this->locale) . '.xlf';
    $translator->addLoader('xlf', new XliffFileLoader());
    $translator
    ->addResource('xlf', $vendor_directory . self::FORM_TRANSLATIONS_DIR . $file,
        $this->locale, self::TRANSLATION_DOMAIN);
    $translator
    ->addResource('xlf', $vendor_directory . self::VALIDATOR_TRANSLATIONS_DIR . $file,
        $this->locale, self::TRANSLATION_DOMAIN);
    return $translator;
  }
}