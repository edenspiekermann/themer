<?php
/**
 * Themer
 *
 * A Tumblr theme parser for local development.
 *
 * @package   Themer
 * @author    Braden Schaeffer 
 * @version   beta
 * @link      http://github.com/bschaeffer/themer
 *
 * @copyright Copyright (c) 2011
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 *
 * @filesource
 */

use Themer\Parser;
use Themer\View;
use Themer\Error;

class Themer {
  
  const VERSION = '0.1.0';
  
  public static $pwd = '';
  public static $home = '';
  public static $theme_file = 'theme.html';
  public static $theme_path = '';
  
  /**
   * Run the Themer parsing library.
   * 
   * @static
   * @access  public
   * @param   string  the project path
   * @return  void
   */
  public static function run($path = '')
  {
    self::_set_themer_paths($path);
    
    if(isset($_GET['themer_asset']))
    {
      View::load_asset($_GET['themer_asset']);
      return;
    }
    
    if( ! isset($_GET['theme']))
    {
      View::load_application();
      return;
    }
    
    $theme = self::load_theme();
    $theme = Parser::parse($theme);
    View::display_html($theme);
  }
  
  /**
   * Parses the project directory and the theme file path.
   *
   * @static
   * @access  private
   * @param   string  the potential file path
   * @return  void 
   */
  private static function _set_themer_paths($path = '')
  {
    if(empty($path) && empty(static::$pwd))
    {
      Error::display('a path to the project directory is required.');
    }
    
    $path = (empty($path)) ? static::$pwd : $path;
    
    if(@is_file($path))
    { 
      static::$pwd = rtrim(dirname($path), '/').'/';
      static::$theme_file = basename($path);
    }
    
    if(empty(self::$theme_file))
    {
      Error::display('a theme_file name is required');
    }
    
    static::$pwd = rtrim($path, '/').'/';
    
    static::$theme_path = static::$pwd.static::$theme_file;
    
    if( ! @file_exists(static::$theme_path))
    {
      Error::display("the theme file `".static::$theme_file."` could not be found in $path");
    }
    
    // Set the $HOME directory
    if(isset($_SERVER['HOME']))
    {
      static::$home = rtrim($_SERVER['HOME'], '/').'/';
    }
  }
  
  /**
   * Loads the theme from a specified path.
   * 
   * @static
   * @access  public
   * @return  string  the theme contents
   */
  public static function load_theme()
  {
    return file_get_contents(static::$theme_path);
  }
}

/* End of file base.php */
/* Location: ./themer/base.php */