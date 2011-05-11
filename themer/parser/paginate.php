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

use Themer\Data;
use Themer\Parser;
use Themer\Router;

/**
 * Themer Paginate Class 
 *
 * Renders pagination data and template tags
 *
 * @package     Themer
 * @subpackage  Parser
 * @author      Braden Schaeffer
 */
class Paginate {
  
  const BLOCK_INDEX = 'Pagination';
  const BLOCK_PERMALINK = 'PermalinkPagination';

  public static $page_number  = 1;
  public static $per_page     = 6;
  
  protected static $_index_pages = array('Index', 'Tag', 'Search', 'Day');
  
  /**
   * Renders pagination data into the theme
   *
   * @access  public
   * @param   string  the theme contents to parse
   * @return  string  the parsed theme contents
   */
  public static function render($theme)
  { 
    $current_page_name = Parser\Pages::$page;
    
    if(in_array($current_page_name, static::$_index_pages))
    {
      $theme = self::index($theme);
    }
    elseif($current_page_name === 'Permalink')
    {
      $theme = self::permalink($theme);
    }
    
    return $theme;
  }
  
  /**
   * Renders pagination data for index pages
   *
   * @access  public
   * @param   string  the theme contents to parse
   * @return  string  the parsed theme contents
   */
  public static function index($theme)
  {
    $theme = Block::remove($theme, self::BLOCK_PERMALINK);
    
    if(count(Parser::$data['Posts']) <= static::$per_page)
    {
      $theme = Block::remove($theme, self::BLOCK_INDEX);
      return $theme;
    }
  
    $data = static::_parse_pages(static::$page_number, Parser::$data['_per_page']);
    
    return self::_render_pagination($theme, self::BLOCK_INDEX, $data);
  }
  
  /**
   * Renders pagination data for permalink pages
   *
   * @access  public
   * @param   string  the theme contents to parse
   * @return  string  the parsed theme contents
   */
  public static function permalink($theme)
  {
    $theme = Block::remove($theme, self::BLOCK_INDEX);
    
    if(count(Parser::$post_data) != 1)
    {
      $theme = Block::remove($theme, self::BLOCK_PERMALINK);
      return $theme;
    }
    
    // Let's find the next/previous post params and set the pagination data
    $next = '';
    $previous = ''; 
    
    if(count(Parser::$post_data) > 1)
    {
      $next_key = Parser::$post_data[0]['_post_array_key'] + 1;
      $post = Data::find('Posts', array('_post_array_key' => $next_key));
    
      if( ! empty($post))
      {
        $next = '/post/'.$post[0]['PostID'];
      }
    
      $previous_key = Parser::$post_data[0]['_post_array_key'] - 1;
      $post = Data::find('Posts', array('_post_array_key' => $previous_key));
    
      if( ! empty($post))
      {
        $previous = '/post/'.$post[0]['PostID'];
      }
    }
    
    $data = array(
      'NextPost'      => $next,
      'PreviousPost'  => $previous
    );
  
    return self::_render_pagination($theme, self::BLOCK_PERMALINK, $data);
  }
  
  /**
   * Renders the specific page data based on the block name and data
   * passed
   * 
   * @access  public
   * @param   string  the theme contents to parse
   * @param   string  the name of the page block
   * @param   array   the page data to parse with
   * @return  string  the parsed theme
   */
  private static function _render_pagination($theme, $block_name, $data)
  {
    foreach(Block::find($theme, $block_name) as $block)
    {
      $tmp = Block::render($block, $block_name);
      
      foreach($data as $k => $v)
      {
        // If the value is empty, just remove the block
        if(empty($v))
        {
          $tmp = Block::remove($tmp, $k);
        }
        else
        {
          $tmp = Block::render($tmp, $k);
          $tmp = Variable::render($tmp, $k, $v);
        }
      }
      
      $theme = str_replace($block, $tmp, $theme);
    }
    
    return $theme;
  }
  
  private static function _parse_pages($current_page = 1, $per_page = 6)
  {
    $all_posts = Parser::$data['Posts'];
    
    $total = count($all_posts);
    $total_pages = ceil($total / $per_page);
    
    // If we are asking for a page number that's not found...
    // the page is not found :)
    
    if($current_page > $total_pages)
    {
      Router::not_found();
    }
    
    // We need to figure out where to start the post offset...
    $start = ($current_page == 1) ? 0 : ($current_page - 1) * $per_page;
    
    // then we clip from the beginning of the post data...
    $clipped = array_slice($all_posts, $start, count($all_posts));
    
    // then we clip the remaining post so we can have a 'page'...
    $final = array_slice($clipped, 0, $per_page);
  
    // and finally we set the new, paginated post data as the data to be parsed
    Parser::$post_data = $final;
  
    $next_page = '';
    $previous_page = '';
  
    if($current_page != 1)
    {
      $previous = $current_page - 1;
      $previous_page = '/page/'.$previous;
    }
    
    if($current_page != $total_pages)
    {
      $next = $current_page + 1;
      $next_page = '/page/'.$next;
    }
  
    
    $data = array(
      'TotalPages'    => $total_pages,
      'CurrentPage'   => $current_page,
      'NextPage'      => $next_page,
      'PreviousPage'  => $previous_page
    );
    
    return $data;
  }
}

/* End of file paginate.php */
/* Location: ./themer/parser/paginate.php */