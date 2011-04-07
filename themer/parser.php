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
use Themer\Parser\Paginate;
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
  
  public static $lang = 'en';
  
  public static $data = array();
  public static $post_data = array();
  
  private static $_theme = '';
  
  /**
   * Parses the Tumblr theme
   *
   * @access  public
   * @param   string  the theme contents to parse
   * @return  string  the parsed theme
   */
  public static function parse($theme = '')
  {
    if(empty($theme) && empty(static::$_theme))
    {
      return '';
    }
    
    $theme = (empty($theme)) ? static::$_theme : $theme;
    
    # Language tags must be parsed first due to that fact that they are
    # interpolated with other template tags and those variables depend on being
    # parsed while the parsing is good (ie. when data is present).
    $theme = Lang::render($theme);
    
    # Meta data should be rendered second incase the theme designer wants to use
    # colors and images multiple times inside other template blocks.
    $theme = Meta::render($theme);
    
    # The order in which these are rendered doesn't really matter.
    $theme = Paginate::render($theme);
    $theme = Pages::render($theme);
    
    # Posts should be rendered second to last. If it weren't for some tag name
    # clashes (ie. {Title} for both blog and text post title), they would be
    # rendered dead last. Second to last at least allows all other blocks, and 
    # theme tags to be rendered into/alongside with each post (if desired).
    $theme = Posts::render($theme, static::$post_data);
    
    return $theme;
  }
  
  /**
   * Set's 404 not found page data, which is basically a single text
   * post with some pre-populated data, then loads the theme
   * 
   * @static
   * @access  private
   * @return  void
   */
  public static function not_found()
  {
    Pages::$page = 'Permalink';
    Pages::$page_data = array();
    
    self::$data['posts'] = array(
      array(
        'PostType'  => 'text',
        'PostID'    => '404-not-found',
        'Title'     => 'Not Found',
        'Body'      => 'The URL you requested could not be found.'
      )
    );
    
    self::render();
    exit(1);
  }
}

/* End of file parser.php */
/* Location: ./themer/parser.php */