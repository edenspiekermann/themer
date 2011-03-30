<?php
/**
 * Themer
 *
 * A Tumblr theme parser for local development.
 *
 * @package   Themer
 * @author    Braden Schaeffer <braden.schaeffer@gmail.com>
 * @version   0.1.0
 * @link      http://github.com/bschaeffer/themer
 *
 * @copyright Copyright (c) 2011
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 *
 * @filesource
 */

namespace Themer;

use Themer\Tumblr\Templatize;

/**
 * Tumblr API Wrapper 
 *
 * A wrapper for Tumblr's API.
 *
 * @package     Themer
 * @subpackage  Tumblr
 * @author      Braden Schaeffer <braden.schaeffer@gmail.com>
 */
class Tumblr {
  
  /**
   * Initialize an API request
   * 
   * @access  public
   * @param   string  the url or username for the Tumblr blog
   * @param   bool   whether or not to 'templatize' the results
   * @return  mixed
   */
  public static function all($username, $templatize = FALSE)
  {
    $blog = self::read($username);
    
    if( ! $templatize)
    {
      return $blog;
    }
    
    $data = Templatize::blog($blog['tumblelog']);
    $data['posts'] = Templatize::posts($blog['posts']);
    
    return $data;
  }
  
  /**
   * Handles an API Request to Tumblr for blog/post information for the
   * given user
   * 
   * @static
   * @access  public
   * @param   string  the username
   * @param   array   the options to include in the request
   * @return  array   the blog and post data
   */
  public static function read($username, $options = array())
  {
    $url = self::_parse_url($username, 'read/json');
    $data = self::_curl($url, $options);
    
    return $data;
  }
  
  /**
   * Makes a cURL request to the given url
   * 
   * @static
   * @access  private
   * @param   string  the url to make the request
   * @param   array   the options to include in the request
   * @param   bool    whether the request is a POST or not
   * @param   array   the post data
   * @return  array   the resulting data
   */
  private static function _curl($url, $options = array())
  {
    if(! empty($options))
    {
      $url .= "?".http_build_query($options);
    }
    
    $c = curl_init($url);
    
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($c);
		curl_close($c);
  	
  	$output = str_replace('var tumblr_api_read = ', '', $output);
  	$output = rtrim(trim($output), ';');
    $output = json_decode($output, TRUE);
    
    return $output;
  }
  
  /**
   * Parses the passed target into a valid tumblr API endpoint.
   * 
   * If it starts with http, we assume a direct url, else we automatically
   * assume a username
   * 
   * @access  public
   * @param   string  the url or username for the Tumblr blog
   * @param   string  the type of data (ie 'read', 'pages', etc...)
   * @return  string  the Tumblr API endpoint in url form
   */
  private static function _parse_url($target = '', $type = 'read')
  {
    if( ! empty($target))
    {
      return "http://$target.tumblr.com/api/$type";
    }
    
    return "http://tumblr.com/api/$type";
  }
}

/* End of file tumblr.php */
/* Location: ./themer/tumblr.php */