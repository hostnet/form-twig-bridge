<?php
namespace Hostnet\FormTwigBridge;

/**
 * Hack to find out the vendor directory.
 * Since the paths differ in a direct clone vs install through composer
 */
class VendorDirectoryFixer
{
  private $vendor_directory;

  private $complete_symfony_checkout;

  public function __construct()
  {
    $vendor_directory = __DIR__ . '/../../../../../../vendor/';
    if(is_dir($vendor_directory)) {
      $this->vendor_directory = $vendor_directory;
    } else {
      // Fall back to the directly cloned path
      $this->vendor_directory = __DIR__ . '/../../../vendor/';
    }
    $this->complete_symfony_checkout = is_dir($this->vendor_directory . '/symfony/symfony');
  }

  /**
   * This plugin is mostly useful if it's included without the symfony framework
   * But this makes sure it works if included with a full Symfony2 installation
   * @param string $component_name
   * @param string $path_within_package
   * @return string The actual location
   */
  public function getLocation($component_name, $path_within_package)
  {
    if($this->complete_symfony_checkout) {
      $path = $this->vendor_directory . '/symfony/symfony/src';
    } else {
      $path = $this->vendor_directory . '/symfony/' . $component_name;
    }
    return $path . $path_within_package;
  }
}