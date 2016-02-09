UPGRADE FROM 1.x to 2.0
=======================

### Dependencies

 * The `hostnet/form-twig-bridge` depends on Symfony 3.
 * PHP requirement has been set on >=5.6 (might work, but we did not verify)

### PHPRenderer

 * Removed `renderEnctype`
 * Added `renderStart` and `renderEnd` methods
