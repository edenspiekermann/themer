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
    $theme = self::render_pages($theme);
    
    # Posts should be rendered second to last. If it weren't for some tag name
    # clashes (ie. {Title} for both blog and text post title), they would be
    # rendered dead last. Second to last at least allows all other blocks, and 
    # theme tags to be rendered into/alongside with each post (if desired).
    $theme = Posts::render($theme, static::$post_data);
    
    # Finally, parse all final theme data
    $theme = self::render_blog($theme);
    
    return $theme;
  }
  
  /**
   * Renders all final template variables and blocks
   * 
   * @access  public
   * @param   string  the theme contents to parse
   * @return  string  the parsed theme contents
   */
  public static function render_blog($theme)
  {
    $community = array(
      'AskEnabled' => 'AskLabel',
      'SubmissionsEnabled' => 'SubmitLabel'
    );
    
    // Render some community variable
    foreach($community as $block => $var)
    {
      if(static::$data[$block])
      {
        $theme = Block::render($theme, $block);
        $theme = Variable::render($theme, $var, static::$data[$var]);
      }
      else
      {
        $theme = Block::remove($theme, $block);
      }
    }
    
    // Render twitter
    if( ! empty(static::$data['TwitterUsername']))
    {
      $theme = Block::render($theme, 'Twitter');
      $theme = Variable::render($theme, 'TwitterUsername', static::$data['TwitterUsername']);
    }
    else
    {
      $theme = Block::remove($theme, 'Twitter');
    }
    
    // Render the blog description
    if( ! empty(static::$data['Description']))
    {
      $theme = Block::render($theme, 'Description');
      $theme = Variable::render($theme, 'Description', static::$data['Description']);
      $theme = Variable::render($theme, 'MetaDescription', static::$data['MetaDescription']);
    }
    else
    {
      $theme = Block::remove($theme, 'Description');
    }
    
    // Render other single tags
    foreach(array( 'Title', 'RSS', 'Favicon', 'CustomCSS') as $var)
    {
      $theme = Variable::render($theme, $var, static::$data[$var]);
    }
    
    // Render the portrait urls
    foreach(array(16, 24, 30, 40, 48, 64, 96, 128) as $size)
    {
      $portrait = 'PortraitURL-'.$size;
      $theme = Variable::render($theme, $portrait, static::$data[$portrait]);
    }
    
    return $theme;
  }
  
  /**
   * Renders Tumblr's {block:HasPages} blocks
   * 
   * @access  public
   * @param   string  the theme contents to parse
   * @return  string  the parsed theme contents
   */
  public static function render_pages($theme)
  { 
    $data = isset(static::$data['Pages']) ? static::$data['Pages'] : array();
    
    if(empty($data))
    {
      return Block::remove($theme, 'HasPages');
    }
    
    foreach(Block::find($theme, 'HasPages') as $has_pages)
    {
      // Set a temporary, rendered HasPages block
      $tmp = Block::render($has_pages, 'HasPages');
  
      // Find all the {block:Pages} blocks in the current block
      foreach(Block::find($tmp, 'Pages') as $page)
      {
        // Clear what has already been rendered for each page
        $rendered = '';
        
        foreach($data as $page_data)
        {  
          // Render {block:Pages} for each page
          $block = Block::render($page, 'Pages');
          
          foreach($page_data as $tag => $value)
          {
            $block = Variable::render($block, $tag, $value);
          }
          
          // Appened the rendered content
          $rendered .= $block;
        }
        
        // Replace the original {block:HasPages} block with rendered content
        $tmp = str_replace($page, $rendered, $tmp);
      }
      
      // Add it to the theme
      $theme = str_replace($has_pages, $tmp, $theme);
    }
    
    return $theme;
  }
}

/* End of file parser.php */
/* Location: ./themer/parser.php */