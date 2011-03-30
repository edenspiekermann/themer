<?php
/**
 * Themer
 *
 * A Tumblr theme parser for local development.
 *
 * @package   Themer
 * @author    Braden Schaeffer <braden.schaeffer@gmail.com>
 * @version   0.1.0
 * @link      http://github.com/bschaeffer/themer
 *
 * @copyright Copyright (c) 2011
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 *
 * @filesource
 */

// We are either being required from source
if(strpos('@php_bin@', '@php_bin') === 0)
{
  define('THEMER_BASEPATH', __DIR__.'/');
}
// or a PEAR installation
else
{
  define('THEMER_BASEPATH', 'themer/');
}

/*-------------------------------------------------------
* Useful CONSTANTS
-------------------------------------------------------*/

define('EXT', '.php');
define('EOL', PHP_EOL);

/*-------------------------------------------------------
* Require the two base Themer classes
-------------------------------------------------------*/

require THEMER_BASEPATH.'themer/autoloader.php';
require THEMER_BASEPATH.'themer/base.php';

set_exception_handler('Themer\Error::exception_handler');
set_error_handler('Themer\Error::php_error');

/* End of file themer.php */
/* Location: ./themer.php */