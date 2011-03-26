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
namespace Themer\Tumblr;

/**
 * Themer Templatize Class 
 *
 * Converts Tumblr API data keys into Tumblr template tags
 *
 * @package     Themer
 * @subpackage  Tumblr
 * @author      Braden Schaeffer <braden.schaeffer@gmail.com>
 */
class Templatize {
  
  protected static $_blog_template = array(
    'Title'           => array('title', 'Untitled'),
    'Description'     => 'description',
    'MetaDescription' => 'description',
    'RSS'             => array('rss', '/rss'),
  );
  
  protected static $_single_post_template = array(
    'PostID'      => 'id',
    'PostType'    => 'type',
    'Permalink'   => 'url-with-slug',
    'Bookmarklet' => array('bookmarklet', 0),
    'Mobile'      => array('mobile', 0),
    'Tags'        => array('tags', array()),
    'DayOfMonth'          => array('unix-timestamp', '', 'self::_time', 'j'),
    'DayOfMonthWithZero'  => array('unix-timestamp', '', 'self::_time', 'd'),
    'DayOfWeek'           => array('unix-timestamp', '', 'self::_time', 'l'),
    'ShortDayOfWeek'      => array('unix-timestamp', '', 'self::_time', 'D'),
    'DayOfWeekNumber'     => array('unix-timestamp', '', 'self::_time', 'N'),
    'DayOfMonthSuffix'    => array('unix-timestamp', '', 'self::_time', 'S'),
    'DayOfYear'           => array('unix-timestamp', '', 'self::_time', 'z'),
    'WeekOfYear'          => array('unix-timestamp', '', 'self::_time', 'W'),
    'Month'               => array('unix-timestamp', '', 'self::_time', 'F'),
    'ShortMonth'          => array('unix-timestamp', '', 'self::_time', 'M'),
    'MonthNumber'         => array('unix-timestamp', '', 'self::_time', 'n'),
    'MonthNumberWithZero' => array('unix-timestamp', '', 'self::_time', 'm'),
    'Year'                => array('unix-timestamp', '', 'self::_time', 'Y'),
    'ShortYear'           => array('unix-timestamp', '', 'self::_time', 'y'),
    'AmPm'                => array('unix-timestamp', '', 'self::_time', 'a'),
    'CapitalAmPm'         => array('unix-timestamp', '', 'self::_time', 'A'),
    '12Hour'              => array('unix-timestamp', '', 'self::_time', 'g'),
    '24Hour'              => array('unix-timestamp', '', 'self::_time', 'G'),
    '12HourWithZero'      => array('unix-timestamp', '', 'self::_time', 'h'),
    '24HourWithZero'      => array('unix-timestamp', '', 'self::_time', 'H'),
    'Minutes'             => array('unix-timestamp', '', 'self::_time', 'i'),
    'Seconds'             => array('unix-timestamp', '', 'self::_time', 'j'),
    'Beats'               => array('unix-timestamp', '', 'self::_time', 'B'),
    'Timestamp'           => 'unix-timestamp',
    'TimeAgo'             => array('unix-timestamp', '', 'self::_timeago'),
  );
  
  protected static $_answer_post_template = array(
    'Question'  => 'question',
    'Answer'    => 'answer',
    'Asker'     => array('asker', 'Anonymous'),
  );
  
  protected static $_audio_post_template = array(
    'Caption'   => 'audio-caption',
    'Artist'    => 'id3-artist',
    'Album'     => 'id3-album',
    'TrackName' => 'id3-title',
    'PlayCount' => array('audio-plays', 0),
    'FormmatedPlayCount'  => array('audio-plays', 0, 'number_format'),
    'PlayCountWithLable'  => array('audio-plays', 0, 'self::_playcount_label'),
  );
  
  protected static $_link_post_template = array(
    'Name'        => 'link-text',
    'URL'         => 'link-url',
    'Description' => 'link-description',
  );
  
  protected static $_photo_post_template = array(
    'Caption'           => 'caption',
    'LinkURL'           => 'photo-link-url',
    'PhotoAlt'          => array('caption', '', 'htmlspecialchars'),
    'PhotoURL-500'      => 'photo-url-500',
    'PhotoURL-400'      => 'photo-url-400',
    'PhotoURL-250'      => 'photo-url-250',
    'PhotoURL-100'      => 'photo-url-100',
    'PhotoURL-75sq'     => 'photo-url-75',
    'PhotoURL-HighRes'  => 'photo-url-1280',
  );
  
  protected static $_photoset_post_template = array(
    'Caption'       => 'caption',
    'Photoset-500'  => array('photos', array(), 'self::_parse_photoset', '500'),
    'Photoset-400'  => array('photos', array(), 'self::_parse_photoset', '400'),
    'Photoset-250'  => array('photos', array(), 'self::_parse_photoset', '250')
  );
  
  protected static $_quote_post_template = array(
    'Quote'   => array('quote-text', ''),
    'Length'  => array('_blank', 'medium'),
    'Source'  => array('quote-source', ''),
  );
  
  protected static $_text_post_template = array(
    'Title' => 'regular-title',
    'Body'  => 'regular-body'
  );
  
  // --------------------------------------------------------------------
  
  /**
   * Converts blog data keys to Tumblr template Tags
   * 
   * @static
   * @access  public
   * @param   array   the array to convert
   * @return  array   the array with converted keys
   */
  public static function blog($data)
  {
    return self::_templatize_with(static::$_blog_template, $data);
  }
  
  /**
   * Converts post data keys to Tumblr template Tags
   * 
   * @static
   * @access  public
   * @param   array   the array to convert
   * @return  array   the array with converted keys
   */
  public static function posts($data)
  {
    $posts = array();
    
    foreach($data as $k => $post)
    {
      $same = array();
      $unique = array();
      
      switch($post['type'])
      {
        case 'photo':
          $post['type'] = (empty($post['photos'])) ? 'photo' : 'photoset';
          break;
          
        case 'regular':
          $post['type'] = 'text';
          break;
      }
      
      $template = "_{$post['type']}_post_template";
      
      $same = self::_templatize_with(static::$_single_post_template, $post);
      
      if(isset(static::${$template}))
      {
        $unique = self::_templatize_with(static::${$template}, $post);
      }
      
      $posts[$k] = array_merge($same, $unique);
    }
    
    return $posts;
  }
  
  /**
   * Converts blog data keys to Tumblr template Tags
   * 
   * @static
   * @access  private
   * @param   array   the array to convert
   * @return  array   the array with converted keys
   */
  private static function _templatize_with($template, $data)
  {
    $new = array();
    
    foreach($template as $k => $v)
    {
      $index = (is_array($v)) ? $v[0] : $v;
      $default = (is_array($v)) ? $v[1] : '';
      $callback = NULL;
      
      if(is_array($v) && isset($v[2]) && is_callable($v[2]))
      {
        $callback = $v[2];
      }
      
      if(isset($data[$index]) && ! empty($data[$index]))
      {
        $new[$k] = $data[$index];
      }
      else
      {
        $new[$k] = $default;
      }
      
      if( ! empty($callback))
      {
        $param = (isset($v[3])) ? $v[3] : NULL;
        
        $new[$k] = call_user_func_array($callback, array($new[$k], $param));
      }
    }
    
    return $new;
  }
  
  /**
   * Sorts the photos into groups by size
   *
   * @access  private
   * @param   array   the photos
   * @param   int     the photo size to group by
   * @return  array   the grouped photos
   */
  private static function _parse_photoset($size, $data)
  {
    $matches = array();
    
    foreach($data as $k => $photos)
    {
      foreach($photos as $type => $url)
      {
        if(preg_match("/$size/", $type))
        {
          $matches[] = $url;
        }
      }
    }
    
    return $matches;
  }
  
  /**
   * Formats the audio play Count
   *
   * @access  private
   * @param   int     the number of plays 
   * @param   bool    whether to include a label or not
   * @return  string  the formatted play count
   */
  private static function _playcount_label($plays)
  {
    if((int)$plays == 0)
    {
      return "0 plays";
    }
    
    return number_format((int)$plays)." plays";
  }
  
  /**
   * Parses a timestamp using the passed flag
   *
   * @access  private
   * @param   int     the timestamp
   * @param   string  the date flag to use
   * @return  mized   the specific representation of the time
   */
  private static function _time($time, $flag)
  {
    return date($flag, $time);
  }
  
  /**
   * Turns a timestamp into relative time
   *
   * @access  private
   * @param   int     the timestamp
   * @return  string  a relative representation of time passed
   */
  private static function _timeago($time)
  { 
    $plural = function ($diff) {
      return ($diff != 1) ? 's' : '';
    };
    
    $diff = time() - $time;
    
    if($diff < 60)
      return $diff . " second" . $plural($diff) . " ago";
    
    $diff = round($diff / 60);
    
  	if($diff < 60)
  		return $diff . " minute" . $plural($diff) . " ago";
    
    $diff = round($diff / 60);
    
    if($diff < 24)
      return $diff . " hour" . $plural($diff) . " ago";
    	
    $diff = round($diff / 24);
    
    if($diff < 7)
  		return $diff . " day" . $plural($diff) . " ago";

  	$diff = round($diff / 7);
    
    if($diff < 4)
  		return $diff . " week" . $plural($diff) . " ago";
    
    return "on " . date("F j, Y", $time);
  }
}

/* End of file templatize.php */
/* Location: ./themer/tumblr/templatize.php */