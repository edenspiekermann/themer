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
  
  protected static $_valid_pages = array('day', 'post', 'search', 'tagged');
  
  public static $uri = '';
  public static $segments = array();
  
  public static $page = 'Index';
  public static $data = array(
    'posts' => array(),
    'page'  => array(),
  );
  
  public static function route()
  { 
    self::_set_uri();
    $segments = self::_set_segments();
    
    if(empty($segments))
    {
      Load::application();
    }
    
    $action = array_shift($segments);
    
    if($action === 'themer_asset')
    {
      Load::asset(implode($segments, '/'));
    }
    
    if(in_array($action, static::$_valid_pages))
    {
      $func = "self::_route_".$action;
      call_user_func($func, $segments);
      return;
    }
    
    self::_not_found();
  }
  
  /**
   * Day page routing information
   *
   * @static
   * @access  private
   * @param   array   the year, month and day to filter
   * @return  void
   */
  private static function _route_day($segments)
  {
    // If there are not at least three segments, it's not a valid day page
    if( ! isset($segments[2]))
    {
      self::_not_found();
    }
  
    $params = array(
      'Year'                => $segments[0],
      'MonthNumberWithZero' => $segments[1],
      'DayOfMonth'          => $segments[2],
    );
    
    $post_data = Data::find('posts', $params);
    
    if(empty($post_data))
    {
      self::_not_found();
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
   * Permalink page routing information
   * 
   * @static
   * @access  private
   * @param   array   the uri segments
   * @return  void
   */
  private static function _route_post($segments)
  {
    if( ! isset($segments[0]))
    {
      self::_not_found();
    }
    
    $post_data = Data::find('posts', array('PostID' => $segments[0]));
    
    if(empty($post_data))
    {
      self::_not_found();
    }
    
    self::_set_data('Permalink', $post_data, array());
  }
  
  /**
   * Search page routing
   * 
   * @access  private
   * @param   array   the uri segments
   * @return  string  the parsed template
   */
  private static function _route_search($segments)
  {
    if(empty($segments))
    {
      Parser::not_found();
    }
    
    $query = $segments[0];
    $query = urldecode(str_replace(array('%20', '+'), ' ', $query));
    $safe  = urlencode(str_replace(array(' ', '%20'), '+', $query));
    
    if(isset($segments))
    {
      
    }
    
    
    $post_data = Data::find('posts', array('Tags' => $query), TRUE);
    
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
   * @access  private
   * @param   array the uri segments
   * @return  void
   */
 private static function _route_tagged($segments)
  {
    if( ! isset($segments[0]))
    {
      self::_not_found();
    }
    
    $tag = urldecode(str_replace('+', ' ', $segments[0]));
    
    $post_data = Data::find('posts', array('Tags' => $tag), TRUE);
    
    if(empty($post_data))
    {
      self::_not_found();
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
  private static function _not_found()
  {
    \Themer\Parser::set_page('Permalink');
    \Themer\Parser::set_post_data(array(
      array(
        'PostType'  => 'text',
        'Title'     => 'Not Found',
        'Body'      => 'The URL you requested could not be found.'
      )
    ));
    
    \Themer::parse_theme();
    exit(1);
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
    if(empty($page)) $page = 'Index';
    
    Parser::set_page(ucwords($page));
    Parser::set_post_data($post_data);
    Parser::set_page_data($page_data);
  }
  
  // --------------------------------------------------------------------
  
  /*-----------------------------------------------------------------
  * Most of the following code was taken from CodeIgniter Reactor
  * and can be found at http://codeigniter.com
  -----------------------------------------------------------------*/
  
  private static function _set_uri()
  {
    if($uri = self::_detect_uri())
    {
      static::$uri = $uri;
      return;
    }
    
    // Is there a PATH_INFO variable?
    // Note: some servers seem to have trouble with getenv() so we'll test it two ways
    
    $path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
    
    if(trim($path, '/') != '' && $path != "/".SELF)
    {
      static::$uri = $path;
      return;
    }
    
    static::$uri = '';
  }
  
  /**
   * Detects the URI
   * 
   * This function will detect the URI automatically and fix the query
   * string if necessary.
   * 
   * Taken from CodeIgniter Reactor -- codeigniter.com
   * 
   * @static
   * @access  private
   * @return  string  the uri string
   */
  private static function _detect_uri()
  {
    if( ! isset($_SERVER['REQUEST_URI']))
    {
      return '';
    }

    $uri = $_SERVER['REQUEST_URI'];
    
    if(strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
    {
      $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
    }
    elseif(strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
    {
      $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
    }

    // This section ensures that even on servers that require the URI to be in the
    // query string (Nginx) a correct URI is found, and also fixes the QUERY_STRING
    // server var and $_GET array.
    
    if(strncmp($uri, '?/', 2) === 0)
    {
      $uri = substr($uri, 2);
    }
    
    $parts = preg_split('#\?#i', $uri, 2);
    $uri = $parts[0];
    
    if(isset($parts[1]))
    {
      $_SERVER['QUERY_STRING'] = $parts[1];
      parse_str($_SERVER['QUERY_STRING'], $_GET);
    }
    else
    {
      $_SERVER['QUERY_STRING'] = '';
      $_GET = array();
    }
    
    if ($uri == '/' || empty($uri))
    {
      return '/';
    }
        
    $uri = parse_url($uri, PHP_URL_PATH);

    // Do some final cleaning of the URI and return it
    return str_replace(array('//', '../'), '/', trim($uri, '/'));
  }
  
  /**
   * Explode the URI Segments. The individual segments will
   * be stored in the Router::$segments array.
   *
   * @static
   * @access  private
   * @return  void
   */
  private static function _set_segments()
  {
    static::$segments = array();
    
    if(empty(static::$uri))
    {
      return array();
    }
    
    foreach(explode("/", preg_replace("|/*(.+?)/*$|", "\\1", static::$uri)) as $val)
    {
      if($val != '')
      {
        static::$segments[] = $val;
      }
    }
    
    return static::$segments;
  }
}

/* End of file router.php */
/* Location: ./themer/router.php */