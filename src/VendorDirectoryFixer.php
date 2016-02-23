<?php
namespace Hostnet\FormTwigBridge;

use Hostnet\Component\Path\Path;

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
        $this->vendor_directory = Path::VENDOR_DIR . '/';
        $this->complete_symfony_checkout = is_dir($this->vendor_directory . 'symfony/symfony');
    }

    /**
     * This plugin is mostly useful if it's included without the symfony framework
     * But this makes sure it works if included with a full Symfony2 installation
     * @param string $component_name
     * @param string $path_within_component
     * @return string The actual location
     */
    public function getLocation($component_name, $path_within_component)
    {
        if ($this->complete_symfony_checkout) {
            $component_location = $this->vendor_directory . 'symfony/symfony/src';
        } else {
            $component_location = $this->vendor_directory . 'symfony/' . $component_name;
        }
        return $component_location . $path_within_component;
    }
}
