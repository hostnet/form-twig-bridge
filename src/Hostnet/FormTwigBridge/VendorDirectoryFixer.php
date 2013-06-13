<?php
namespace Hostnet\FormTwigBridge;

/**
 * Hack to find out the vendor directory.
 * Since the paths differ in a direct clone vs install through composer
 */
class VendorDirectoryFixer
{
  private $vendor_directory;

  public function __construct()
  {
    $vendor_directory = __DIR__ . '/../../../../../../vendor/';
    if(is_dir($vendor_directory)) {
      $this->vendor_directory = $vendor_directory;
    } else {
      // Fall back to the directly cloned path
      $this->vendor_directory = __DIR__ . '/../../../vendor/';
    }
  }

  public function getVendorDirectory()
  {
    return $this->vendor_directory;
  }
}