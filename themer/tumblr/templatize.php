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
namespace Themer\Tumblr;

/**
 * Themer Templatize Class 
 *
 * Converts Tumblr API data keys into Tumblr template tags
 *
 * @package     Themer
 * @subpackage  Tumblr
 * @author      Braden Schaeffer 
 */
class Templatize {
  
  protected static $_blog_template = array(
    'Title'           => array('title', 'Untitled'),
    'Description'     => 'description',
    'MetaDescription' => array('description', '', 'strip_tags'),
    'RSS'             => array('rss', '/rss'),
  );
  
  protected static $_single_post_template = array(
    'PostID'      => 'id',
    'PostType'    => 'type',
    'Permalink'   => array('id', '/post/%x%'),
    'Bookmarklet' => array('bookmarklet', 0),
    'Mobile'      => array('mobile', 0),
    'Tags'        => array('tags', array()),
    'PostNotesURL'  => array('id', '/notes/%x%'),
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
    'RawAudioURL'         => array('raw-audio-url', '/themer_asset/audio/sample.mp3'),
    'ExternalAudioURL'    => array('external-audio-url', '/themer_asset/audio/sample.mp3'),
    'AudioPlayer'         => 'audio-player',
    'AudioPlayerBlack'    => array('audio-player', '', 'self::_audio_player_black'),
    'AudioPlayerGrey'     => array('audio-player', '', 'self::_audio_player_grey'),  
    'FormattedPlayCount'  => array('audio-plays', 0, 'number_format'),
    'PlayCountWithLabel'  => array('audio-plays', 0, 'self::_playcount_label'),
  );
  
  protected static $_chat_post_template = array(
    'Title' => 'title',
    'Lines' => array('conversation', array(), 'self::_chat_post')
  );
  
  protected static $_link_post_template = array(
    'Name'        => 'link-text',
    'URL'         => 'link-url',
    'Description' => 'link-description',
    'Target'      => 'link-target' // This not an actual API item
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
  
  protected static $_video_post_template = array(
    'Caption'   => 'video-caption',
    'Video-500' => 'video-source',
    'Video-400' => 'video-source',
    'Video-250' => 'video-source',   
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
    return self::with(static::$_blog_template, $data);
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
          
        case 'conversation':
          $post['type'] = 'chat';
          break;
      }
      
      $template = "_{$post['type']}_post_template";
      
      $same = self::with(static::$_single_post_template, $post);
      
      if(isset(static::${$template}))
      {
        $unique = self::with(static::${$template}, $post);
      }
      
      $posts[$k] = array_merge($same, $unique);
      
      // Add a private post array key variable to enable easier permalink
      // pagination
      
      $posts[$k]['_post_array_key'] = $k + 1;
    }
    
    return $posts;
  }
  
  /**
   * Converts blog data keys to Tumblr template Tags
   * 
   * @static
   * @access  public
   * @param   array   the array to convert
   * @return  array   the array with converted keys
   */
  public static function with($template, $data)
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
        if( ! empty($default) && ! is_array($default) && preg_match("/%x%/", $default))
        {
          $new[$k] = str_replace("%x%", $data[$index], $default);
        }
        else
        {
          $new[$k] = $data[$index];
        }
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
   * Formats the audio player as black
   *
   * @access  private
   * @param   string  the audio player
   * @return  string  the formatted formatted black audio player
   */
  private static function _audio_player_black($player)
  {
    return str_replace('audio_player.swf', 'audio_player_black.swf', $player);
  }
  
  /**
   * Formats the audio player as grey
   *
   * @access  private
   * @param   string  the audio player
   * @return  string  the formatted formatted grey audio player
   */
  private static function _audio_player_grey($player)
  {
    return str_replace('&color=FFFFFF', '&color=E4E4E4', $player);
  }
  
  /**
   * Parses a timestamp using the passed flag
   *
   * @access  private
   * @param   int     the timestamp
   * @param   string  the date flag to use
   * @return  mixed   the specific representation of the time
   */
  private static function _time($time, $flag)
  {
    return date($flag, $time);
  }
  
  /**
   * Parses a timestamp using the passed flag
   *
   * @access  private
   * @param   array   the chat information
   * @return  array   the converted chat post keys
   */
  private static function _chat_post($data)
  { 
    $lines = array();
    $names = array();
    
    foreach($data as $k => $v)
    {
      $tmp = array();
      
      $tmp['Name']  = (isset($v['name'])) ? $v['name'] : '';
      $tmp['Lable'] = (isset($v['label'])) ? $v['label'] : '';
      $tmp['Line']  = (isset($v['phrase'])) ? $v['phrase'] : '';
      $tmp['Alt']   = (($k + 1) % 2) ? 'Odd' : 'Even';
      
      if( ! array_key_exists($v['name'], $names))
      {
        $names[$tmp['Name']] = count($names) + 1;
      }
      
      $tmp['UserNumber'] = $names[$tmp['Name']];
    
      $lines[] = $tmp;
    }
    
    return $lines;
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