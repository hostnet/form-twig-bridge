form-twig-bridge
----------------

This package might be nice for you if you
- Want to use the Symfony 2 form component
- With the Twig rendering
- But don't want to use all of Symfony 2

It's inspired by Bernhard Schussek's [standalone-forms](https://github.com/bschussek/standalone-forms/).

If you want to use more then just the form framework, and you're inside a Symfony 1 application, maybe the [hnDependencyInjectionPlugin](https://github.com/hostnet/hnDependencyInjectionPlugin) is more suitable for you.

[![Total Downloads](https://poser.pugx.org/hostnet/form-twig-bridge/downloads.png)](https://packagist.org/packages/hostnet/form-twig-bridge)
[![Build Status](https://travis-ci.org/hostnet/form-twig-bridge.svg?branch=master)](https://travis-ci.org/hostnet/form-twig-bridge)

### Installation
1. [Download Composer][1].
2. Add to your composer.json
  ```
  "require": {
      "hostnet/form-twig-bridge": ">=1.0"
  }

  ```
3. Use the builders to create a FormFactory and a Twig_Environment with the correct configuration:
   ```
   use Hostnet\FormTwigBridge\Builder;
   use Symfony\Component\Security\Csrf\TokenStorage\NativeSessionTokenStorage;
   
   $csrf = new NativeSessionTokenStorage();
   $translator_builder = new TranslatorBuilder();
   // $translator_builder->setLocale('nl_NL'); // Uncomment if you want a non-english locale
   $builder = new Builder();
   $environment = $builder
       ->setCsrfTokenManager($csrf)
       ->setTranslator($translator_builder->build())
       ->createTwigEnvironmentBuilder()->build();
   $factory = $builder->buildFormFactory();
   ```
5. Use the form factory to create your form, see the [symfony docs](http://symfony.com/doc/current/book/forms.html).
6. If you use Twig templates: Use the form factory and the twig environment like you'd normally do
7. If you use PHP templates, use the [public methods](https://github.com/hostnet/form-twig-bridge/blob/master/src/Hostnet/FormTwigBridge/PhpRenderer.php) of the PhpRenderer.
   Initialize it with ```new PhpRenderer($twig_environment)```

### Optional configuration options

Builder
- ```enableAnnotationMapping``` enables doctrine annotation mapping (requires [doctrine/annotations](https://packagist.org/packages/doctrine/annotations))
- ```addFormExtension``` adds your custom form extensions

TwigEnvironmentBuilder
- ```prependTwigLoader``` adds additional twig loaders that are called before the loader added by the form-twig-bridge. You can always add loaders yourself, even after building.
- ```setFormTheme``` for a custom form theme, note that you will have to add a loader that loads your form theme

### Changelog

1.1.0
- Now works with Symfony 2.7.0
- Deprecated `setCsrfProvider` in favour of `setCsrfTokenManager` for Symfony 3.0 compatibility

1.0.1
- Can now be used inside a full-fletched Symfony2 framework.

1.0
- Upgraded from Symfony 2.1 to Symfony 2.3.
- [Breaking] Added ```setTranslator``` to ```Builder```.
- [Breaking] Removed ```setLocale``` from the ```TwigEnvironmentBuilder```.

### Running the unit-tests

1. Clone the repository yourself
2. Go to the directory of the clone
3. Run ```composer.phar install```
4. Run ```phpunit```

[1]: http://getcomposer.org/doc/00-intro.md
