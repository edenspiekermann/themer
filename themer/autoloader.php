<?php
/**
 * Themer
 *
 * A Tumblr theme parser for local development.
 *
 * @package   Themer
 * @author    Braden Schaeffer 
 * @version   0.1.0
 * @link      http://github.com/bschaeffer/themer
 *
 * @copyright Copyright (c) 2011
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 *
 * @filesource
 */

namespace Themer;

/**
 * Themer Autoloader Class 
 *
 * A PHP5 autoloader.
 *
 * @package   Themer
 * @author    Braden Schaeffer 
 */

class Autoloader {
  
  public static $loaded = array();

	/**
	 * This function is registered as an autoloader and prepended to the
	 * begginning of the autoload stack.
	 * 
	 * @static
	 * @access  public
	 * @param   string  the fully namespaced class name
	 * @return  bool    whether the class was loaded or not (required by PHP)  
	 */
	public static function load($class)
	{
		$load_class = str_replace('\\', '/', strtolower($class));
		
		if(in_array($class, static::$loaded) || class_exists($class))
		{
			return TRUE;
		}
		
		$path = THEMER_BASEPATH.$load_class.EXT;
		
		if(file_exists($path))
		{	
			array_push(static::$loaded, $class);
			require_once $path;
			self::_init_class($class);
			return TRUE;
		}

		return FALSE;
	}
	
	/**
	 * Calls the '__autoinit' function on an autoloaded class it if exists.
	 * 
	 * @static
	 * @access  private
	 * @param   string  the class we are initializing
	 * @return  void
	 */
	private static function _init_class($class)
	{ 
	  if(method_exists($class, '__autoinit'))
	  {
	    call_user_func(array($class, '__autoinit'));
	  }
	}
}

spl_autoload_register(__NAMESPACE__.'\Autoloader::load', FALSE);

/* End of file autoloader.php */
/* Location: ./themer/autoloader.php */