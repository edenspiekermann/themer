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

namespace Themer;
use Themer\Parser;

/**
 * Themer Loader Class
 * 
 * Load's Themer views and assets
 * 
 * @package   Themer
 * @author    Braden Schaeffer 
 */
class Load {
  
  protected static $asset_headers = array(
    'css' => 'text/css',
    'js'  => 'text/javascript',
    'jpg' => 'image/jpg',
    'png' => 'image/png',
    'gif' => 'image/gif'
  );
  
  private static $_ob_level;
  
  /**
   * Auto-initializer called by the autoloader. Sets the ob level. 
   * 
   * @static
   * @access  public
   * @return  void
   */
  public static function __autoinit()
  {
    static::$_ob_level = ob_get_level();
  }
  
  /**
   * Load the Themer application front.
   * 
   * @static
   * @access  public
   * @return  void
   */
  public static function application()
  {
    $theme_contents = \Themer::load_theme();
    
    $data['metas'] = Parser\Meta::load($theme_contents);
    $data['info'] = array(
      'Title'       => Data::get('Title'),
      'Description' => Data::get('Description')
    );
    
    $themer = static::view('application', array('data' => $data), TRUE);
    
    static::display_html($themer);
  }
  
  /**
   * Loads a specific Themer asset.
   * 
   * @static
   * @access  public
   * @param   string  the asset to load
   * @return  void
   */
  public static function asset($asset_path = '')
  {
    $path = THEMER_BASEPATH.'themer/public/'.$asset_path;
    
    if( ! @file_exists($path))
    {
      self::_not_found($asset_path);
    }
    
    header("HTTP/1.0 200 OK");
    
    $tmp = explode('.', $path);
    $ext = end($tmp);
    
    if(isset(static::$asset_headers[$ext]))
    {
      header("Content-Type: ".static::$asset_headers[$ext], TRUE);
    }
    
    echo file_get_contents($path);
    exit(1);
  }
  
  /**
   * Loads a view for the Themer application. The general idea for his
   * code was taken from CodeIgniter v2.0.0 (beta).
   * 
   * @access  public
   * @param   string  the view file to load
   * @param   array   the optional variables to load
   * @param   bool    whether to return the view or not
   * @return  void
   */
  public static function view($_t_file = '', $_t_vars = array(), $_t_return = FALSE)
  {
    $_t_path = THEMER_BASEPATH.'themer/views/'.$_t_file.'.php';
    
    if( ! @file_exists($_t_path))
    {
      self::_not_found('themer/views/'.$_t_file.'.php');
    }
    
    if(is_array($_t_vars))
    {
      extract($_t_vars);
    }
    
    ob_start();
    
    require $_t_path;
    
    if($_t_return)
    {
      $contents = ob_get_contents();
      @ob_end_clean();
      return $contents;
    }
    
    if(ob_get_level() > static::$_ob_level + 1)
    {
      ob_end_flush();
    }
    else
    {
      echo ob_get_contents();
      @ob_end_clean();
    }
  }
  
  /**
   * Outputs HTML content to the browser.
   * 
   * @static
   * @access  public
   * @param   string  the html content
   * @return  void
   */
  public static function display_html($html = '')
  {
    header("HTTP/1.0 200 OK", TRUE);
    header('Content-Type: text/html', TRUE);
    exit($html);
  }
  
  /**
   * Sets 404 headers and displays a simple message about the file.
   * 
   * @static
   * @access  private
   * @param   string  the file path that could not be loaded
   * @return  void
   */
  private static function _not_found($path = '')
  {
    header('HTTP/1.0 404 Not Found', TRUE);
    exit("<h1>File not found: $path</h1>");
  }
}

/* End of file load.php */
/* Location: ./themer/load.php */