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

use Themer\Data;
use Themer\Parser;
use Themer\Error;
use Themer\Load;

/**
 * Themer Router Class 
 *
 * Parses the requested route, autoloading data along the way
 *
 * @package     Themer
 * @author      Braden Schaeffer
 */
class Router {
  
  public static function route()
  { 
    // Load the klein.php router
    \Themer::load_lib('klein/klein.php');
    
    // Route for themer assets
    respond('/themer_asset/[**:asset]', function($req, $res) {
      $asset = ltrim($req->param('asset'), '/');
      Load::asset($asset);
    });
    
    // Day Pages
    respond('/day/[i:year]/[i:month]/[i:day]', function($req) {
      $dates = $req->params(array('year', 'month', 'day'));
      Router::day($dates);
    });
    
    // Index Pagination
    respond('/page/[i:page_number]', function($req) {
      Router::page($req->page_number);
    });
    
    // Permalink Pages
    respond('/post/[i:post_id]', function($req) {
      Router::post($req->post_id);
    });
    
    // Search Pages
    respond('/search/[:term]/[i:page_number]?', function($req) {
      Router::search($req->term, $req->page_number);
    });
    
    // Tag Pages
    respond('/tagged/[:tag]/[i:page_number]?', function($req) {
      Router::tagged($req->tag, $req->page_number);
    });
    
    // Home Page
    respond('/', function($req) {
      // If there is no ?theme param, load the Themer app front
      if( ! array_key_exists('theme', $req->params())) Load::application();
    });
    
    // 404 Not Found
    respond('*', function ($req, $ig, $nore, $matched) {
      // If we didn't match a route, set some not found "post data"
      if( ! $matched) Router::not_found();
    });

    dispatch(NULL, NULL, NULL, TRUE);
  }
  
  /**
   * Day page routing information
   *
   * @static
   * @access  public
   * @param   array   the year, month and day to filter
   * @return  void
   */
  public static function day($dates)
  {
    $params = array(
      'Year'                => $dates['year'],
      'MonthNumberWithZero' => $dates['month'],
      'DayOfMonth'          => $dates['day'],
    );
    
    $post_data = Data::find('Posts', $params);
    
    if(empty($post_data))
    {
      self::not_found();
    }
  
    $date = strtotime(implode($segments, '-'));
    
    $page_data = array(
      'Year'            => $params['Year'],
      'Month'           => $post_data[0]['Month'],
      'DayOfMonth'      => $post_data[0]['DayOfMonth'],
      'NextDayPage'     => '/day/'.date('Y/m/d', strtotime("+1 day", $date)),
      'PreviousDayPage' => '/day/'.date('Y/m/d', strtotime("-1 day", $date))
    );
  
    self::_set_data('Day', $post_data, $page_data);
  }
  
  /**
   * Pagination routing
   * 
   * @access  public
   * @param   int     the page number
   * @return  void
   */
  public static function page($page)
  {
    Parser\Paginate::$page_number = $page;
    self::_set_data('Index', Data::get('Posts'), array());
  }
  
  /**
   * Permalink page routing information
   * 
   * @static
   * @access  public
   * @param   string  the post id
   * @return  void
   */
  public static function post($post_id)
  {
    $post_data = Data::find('Posts', array('PostID' => $post_id));
    
    if(empty($post_data))
    {
      self::not_found();
    }
    
    self::_set_data('Permalink', $post_data, array());
  }
  
  /**
   * Search page routing
   * 
   * @access  public
   * @param   string  the search term
   * @param   int     the page number to start from
   * @return  void
   */
  public static function search($term, $page = NULL)
  {
    $query = $term;
    $query = urldecode(str_replace(array('%20', '+'), ' ', $query));
    $safe  = urlencode(str_replace(array(' ', '%20'), '+', $query));
    
    if(is_int($page) && $page > 0)
    {
      Parser\Paginate::$page_number = $segments[1];
    }
    
    $post_data = Data::find('Posts', array('Tags' => $query), TRUE);
    
    $page_data = array(
      'SearchQuery'         => $query,
      'URLSafeSearchQuery'  => $safe,
      'SearchResultCount'   => count($post_data)
    );
    
    self::_set_data("Search", $post_data, $page_data);
  }
  
  /**
   * Tag page routing
   * 
   * @static
   * @access  public
   * @param   string  the tag to lookup
   * @param   string  the page to start from
   * @return  void
   */
 public static function tagged($tag, $page = NULL)
  {
    $tag = urldecode(str_replace('+', ' ', $tag));
    
    if(is_int($page) && $page > 0)
    {
      Parser\Paginate::$page_number = $page;
    }
    
    $post_data = Data::find('Posts', array('Tags' => $tag), TRUE);
    
    if(empty($post_data))
    {
      self::not_found();
    }
    
    self::_set_data('Tag', $post_data, $tag);
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
    $post_data = array(
      array(
        'PostType'        => 'text',
        'PostID'          => '404-not-found',
        'Title'           => 'Not Found',
        'Body'            => 'The URL you requested could not be found.',
        '_post_array_key' => '404-not-found'
      )
    );
    
    self::_set_data('Permalink', $post_data);
  }
  
  /**
   * Uniformly set the route data
   *
   * @static
   * @access  private
   * @param   array   the post data
   * @return  void
   */
  private static function _set_data($page = 'Index', $post_data = array(), $page_data = array())
  {
    Parser::$post_data = $post_data;
    Parser\Pages::$page = ucwords($page);
    Parser\Pages::$page_data = $page_data;
  }
}

/* End of file router.php */
/* Location: ./themer/router.php */