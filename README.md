form-twig-bridge
================

This package might be nice for you if you
- Want to use the Symfony 2 form component
- With the Twig rendering
- But don't want to use all of Symfony 2

It's inspired by Bernhard Schussek's [standalone-forms](https://github.com/bschussek/standalone-forms/)

Installation
------------
1. [Download Composer][1].
2. Add to your composer.json
  ```
  "require": {
      "hostnet/form-twig-bridge": "0.*"
  }

  ```
3. Use the builders to create a FormFactory and a Twig_Environment with the correct configuration:
   ```
   use Hostnet\FormTwigBridge\Builder;
   use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;
   
   $csrf = new DefaultCsrfProvider('change this token');
   $builder = new Builder();
   $environment =
      $builder->setCsrfProvider($csrf)->createTwigEnvironmentBuilder()->build();
   $factory = $builder->buildFormFactory();
   ```
4. Use the form factory to create your form, see the [symfony docs](http://symfony.com/doc/current/book/forms.html).
4. If you use Twig templates: Use the form factory and the twig environment like you'd normally do
5. If you use PHP templates, use the [public methods](https://github.com/hostnet/form-twig-bridge/blob/master/src/Hostnet/FormTwigBridge/PHPRenderer.php) of the PHPRenderer.
   Initialize it with ```new PHPRenderer($twig_environment)```

Running the unit-tests
------------
1. Clone the repository yourself
2. Go to the directory of the clone
3. Run ```composer.phar install```
4. Run ```phpunit```

[1]: http://getcomposer.org/doc/00-intro.md
