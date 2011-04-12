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

use Themer\Tumblr\API;
use Themer\Tumblr\Templatize;

/**
 * Tumblr API Wrapper 
 *
 * A wrapper for Tumblr's API.
 *
 * @package     Themer
 * @subpackage  Tumblr
 * @author      Braden Schaeffer 
 */
class Tumblr {
  
  const BLOG_KEY  = 'tumblelog';
  const POSTS_KEY = 'posts';
  
  /**
   * Initialize an API request
   * 
   * @access  public
   * @param   string  the url or username for the Tumblr blog
   * @param   bool    whether or not to 'templatize' the results
   * @return  mixed
   */
  public static function all($username, $templatize = FALSE)
  {
    $results = API::read($username, array('num' => 50));
    
    if( ! $templatize)
    {
      return $results;
    }
    
    $data = Templatize::blog($results[self::BLOG_KEY]);
    $data['Posts'] = Templatize::posts($results[self::POSTS_KEY]);
    
    return $data;
  }
  
  /**
   * Requests blog data only from a Tumblr blog
   *
   * @static
   * @access  public
   * @param   string  the tumblr username (john in john.tumblr.com)
   * @param   bool    whether or not to request JSON data
   * @return  array   the blog data
   */
  public static function blog($username, $json = TRUE)
  {
    $data = API::read($username, NULL, $json);
    echo "<pre>";
    var_dump($data);
    exit();
    return $data[self::BLOG_KEY];
  }
  
  /**
   * Requests post data for a given user's Tumblr blog
   * 
   * @access  public
   * @param   string  the tumblr username (john in john.tumblr.com)
   * @param   array   the request options to use
   * @param   bool    whether or not to request JSON data
   * @return  array   the posts
   */
  public static function posts($username, $options = array(), $json = TRUE)
  {
    $data = API::read($username, $options, $json);
    return $data[self::POSTS_KEY];
  }
  
  /**
   * Sets the class email and password variables
   * 
   * @static
   * @access  public
   * @param   string  the tumblr account email address
   * @param   string  the tumblr account password
   * @return  void
   */
  public static function set_login($email, $password)
  {
    static::$_email = $email;
    static::$_password = $password;
  }
}

/* End of file tumblr.php */
/* Location: ./themer/tumblr.php */