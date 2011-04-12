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
 */
namespace Themer\Tumblr;

use Themer\Tumblr;

/**
 * Themer API Class 
 *
 * Description
 *
 * @package     Themer
 * @subpackage  Tumblr
 * @author      Braden Schaeffer
 */
class API {
  
  const API_READ          = 'read';
  const API_READ_JSON     = 'read/json';
  const API_AUTHENTICATE  = 'authenticate';
  
  const APP_ENDPOINT  = 'http://tumblr.com/api/%s';
  const USER_ENDPOINT = 'http://%s.tumblr.com/api/%s';
  
  /**
   * Makes a Tumblr API read request
   * 
   * @final
   * @static
   * @access  public
   * @param   string  the username for the tumblr account
   * @param   array   the options to include in the request
   * @param   bool    whether or not this is a JSON request
   * @return  array   the resulting data
   */
  final public static function read($username = '', $options = array(), $json = TRUE)
  {
    $type = ($json === TRUE) ? self::API_READ_JSON : self::API_READ;
    $url = self::_parse_url($username, $type);
    
    $results = self::_curl($url, $options);
    
    // Is this a json request? If so, parse the results like so...
    if($json === TRUE)
    {
      $results = self::_json_to_array($results);
    }
    
    return $results;
  }
  
  /**
   * Makes an authenticated request to 
   * 
   * @final
   * @static
   * @access  public
   * @return  void
   */
  final public static function authenticate($email, $password)
  {
    $url = self::_parse_url('', self::API_AUTHENTICATE);
    
    $credentials = array(
      'email' => $email,
      'password' => $password
    );
    
    $result = self::_curl($url, $credentials, TRUE);
    
    return ($result !== FALSE);
  }
  
  /**
   * Makes a cURL request to the given api
   * 
   * @static
   * @access  private
   * @param   string  the api endpoint
   * @param   array   the request data
   * @param   bool    whether this is a POST request or not
   * @return  mixed   the output string from the 
   */
  private static function _curl($url, $options = array(), $is_post = FALSE)
  {    
    if( ! $is_post && ! empty($options))
    {
      $url .= '?'.http_build_query($options);
    }
    
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    if($is_post && ! empty($options))
    {
      curl_setopt($ch, CURLOPT_POST, count($options));
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options));
    }
    
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);
    
    curl_close($ch);
    
    // A 200 status code is the only acceptable result
    if($info['http_code'] != 200) return FALSE;

    return $output;
  }
  
  /**
   * Converts returned API JSON data to a PHP array
   * 
   * @static
   * @access  private
   * @param   string  the JSON data string to convert
   * @return  array   the JSON data as a PHP array
   */
  private static function _json_to_array($json)
  {
    $data = str_replace('var tumblr_api_read = ', '', $json);
    $data = rtrim(trim($data), ';');
    return json_decode($data, TRUE);
  }
  
  /**
   * Parses the passed username and type into a valid tumblr API endpoint.
   * 
   * @static
   * @access  public
   * @param   string  the tumblr username (john in john.tumblr.com)
   * @param   string  the API endpoint to request
   * @return  string  the Tumblr API endpoint in url form
   */
  private static function _parse_url($username = '', $type = 'read')
  {
    if( ! empty($username))
    {
      return sprintf(self::USER_ENDPOINT, $username, $type);
    }
    
    return sprintf(self::APP_ENDPOINT, $type);
  }
}

/* End of file api.php */
/* Location: ./themer/tumblr/api.php */