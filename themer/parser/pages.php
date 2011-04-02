<?php
/**
 * Themer
 *
 * A Tumblr theme parser for local development.
 *
 * @package   Themer
 * @author    Braden Schaeffer <braden.schaeffer@gmail.com>
 * @version   beta
 * @link      http://github.com/bschaeffer/themer
 *
 * @copyright Copyright (c) 2011
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 *
 * @filesource
 */
namespace Themer\Parser;

use Themer\Error;

/**
 * Themer Pages Class 
 *
 * Renders Tumblr {block:ItemPage} blocks
 *
 * @package     Themer
 * @subpackage  Parser
 * @author      Braden Schaeffer <braden.schaeffer@gmail.com>
 */
class Pages {
  
  protected static $_pages = array(
	  'Day'       => array('Day', 'Index'),
	  'Index'     => array('Index'),
 	  'Permalink' => array('Permalink'),
	  'Search'    => array('Search', 'Index'),
	  'Tag'       => array('Tag', 'Index')
	);
  
  /**
   * Renders Tumblr {block:ItemPage} blocks
   * 
   * @static
   * @access  public
   * @param   string  the theme contents to render out
   * @param   string  the page we are rendering
   * @param   array   the page data to use
   * @return  string  the parsed theme
   */
  public static function render($theme, $page = 'Index', $data = array())
  {
    if( ! array_key_exists($page, static::$_pages))
    {
      Error::display("$page is not a valid Tumblr page block.");
    }

    $theme = self::cleanup($theme, $page);
    
    foreach(static::$_pages[$page] as $render)
    {
      $func = "self::".strtolower($render);
      
      if(is_callable($func))
      {
        $theme = call_user_func_array($func, array($theme, $data));
      } 
      
      $theme = Block::render($theme, $render.'Page');
    }
    
    return $theme;
  }
  
  /**
   * Parses block:DayPage blocks
   *
   * @access  public
   * @return  string  the rendered DayPage
   */
  public static function day($theme, $data = array())
  {
    $previous_day_page = (isset($data['PreviousDayPage']) && ! empty($data['PreviousDayPage']));
    $next_day_page = (isset($data['NextDayPage']) && ! empty($data['NextDayPage']));
    
    if($previous_day_page || $next_day_page)
    {
      $theme = Block::render($theme, 'DayPagination');
    }
    
    if($previous_day_page)
    {
      $theme = Block::render($theme, 'PreviousDayPage');
    }
    
    if($next_day_page)
    {
      $theme = Block::render($theme, 'NextDayPage');
    }
    
    $theme = self::_render_page_data($theme, 'Day', $data);
    
    return $theme;
  }
  
  public static function permalink($theme, $data = array())
  {
    return $theme;
  }
  
  /**
   * Render Search pages
   * 
   * Internally, this is going to render posts tagged with the query,
   * but the Template variables will represent those available on search
   * pages.
   * 
   * @access  public
   * @param   string  the block contents
   * @param   string  the search term
   * @return  string  the parsed template
   */
  public static function search($theme, $data)
  {
    if($data['SearchResultCount'] == 0)
    {
      $theme = Block::render($theme, 'NoSearchResults');
    }
    else
    {
      $theme = Block::remove($theme, 'NoSearchResults');
    }
    
    $theme = self::_render_page_data($theme, 'Search', $data);
    
    return $theme;
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Render Tag pages
   *
   * @access  public
   * @param   string  the block contents
   * @param   mixed   the current tag
   * @return  string  the parsed template
   */
  public static function tag($theme, $tag = '')
  {
    $tag = (is_array($tag)) ? $tag[0] : $tag;
    $safe = urlencode($tag);
    $url = '/tagged/'.str_replace(array(' ', '%20'), '+', $safe);
    $chrono = $url.'/chrono';
    
    $page_data = array(
      'Tag'           => $tag,
      'URLSafeTag'    => $safe,
      'TagURL'        => $url,
      'TagURLChrono'  => $chrono
    );
    
    $theme = self::_render_page_data($theme, 'Tag', $page_data);
    
    return $theme;
  }
  
  /**
   * Removes all un-used {block:ItemPage} blocks from the template
   * 
   * @static
   * @access  public
   * @param   string  the block contents to clean
   * @param   string  the page we are rendering
   * @return  string  the cleaned block
   */
  public static function cleanup($block, $page)
  {
    foreach(static::$_pages as $p => $to_render)
    {
      if( ! in_array($p, static::$_pages[$page]))
      {
        $block = Block::remove($block, $p.'Page');
      }
    }
    
    return $block;
  }
  
  /**
   * Renders page data for the specified page block
   *
   * @static
   * @access  private
   * @param   string  the block to render
   * @param   string  the page to render
   * @param   array   the data to use while rendering
   * @return  string  the rendered block
   */
  private static function _render_page_data($block, $page, $data)
  {
    $page = $page.'Page';
    
    $blocks = Block::find($block, $page);
    
    if(empty($blocks))
    {
      return $block;
    }

    foreach($blocks as $b)
    { 
      $tmp = Block::render($b, $page);
      
      foreach($data as $k => $v)
      {
        $tmp = Variable::render($tmp, $k, $v, FALSE);
      }
      
      $block = str_replace($b, $tmp, $block);
    }
    
    return $block;
  }
}

/* End of file pages.php */
/* Location: ./themer/parser/pages.php */