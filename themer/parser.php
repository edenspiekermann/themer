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

use Themer\Parser\Lang;
use Themer\Parser\Meta;
use Themer\Parser\Posts;
use Themer\Parser\Pages;
use Themer\Parser\Block;
use Themer\Parser\Variable;

/**
 * Themer Parser Class 
 *
 * Parses Tumblr theme files.
 *
 * @package     Themer
 * @subpackage  Parser
 * @author      Braden Schaeffer 
 */
class Parser {
  
  private static $current_page = 'Index';
  private static $post_data    = array();
  private static $page_data    = array();
  
  /**
   * Parses the Tumblr theme
   *
   * @access  public
   * @param   string  the theme contents to parse
   * @return  string  the parsed theme
   */
  public static function parse($theme = '')
  {
    if(empty($theme)) return '';
    
    $theme = Lang::render($theme);
    $theme = Pages::render($theme, static::$current_page, static::$page_data);
    $theme = Meta::render($theme);
    $theme = Posts::render($theme, static::$post_data);
    
    return $theme;
  }
  
  /**
   * Sets the page
   *
   * @static
   * @access  public
   * @param   string  the page
   * @return  void
   */
  public static function set_page($page)
  {
    static::$current_page = $page;
  }
  
  /**
   * Sets the post data, if there is any
   *
   * @static
   * @access  public
   * @param   array   the post data
   * @return  void
   */
  public static function set_post_data($data)
  {
    static::$post_data = $data;
  }
  
  /**
   * Sets the page data, if there is any
   *
   * @static
   * @access  public
   * @param   array   the page data
   * @return  void
   */
  public static function set_page_data($data)
  {
    static::$page_data = $data;
  }
}

/* End of file parser.php */
/* Location: ./themer/parser.php */