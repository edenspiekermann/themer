<?php

namespace Themer;

require_once 'SymfonyComponents/YAML/sfYaml.php';

class Data {
  
  public static $paths = array();
  public static $config = array();
  
  /**
   * Auto-initializer for auto-loading some necessary data
   *
   * @static
   * @access  private
   * @return  void
   */
  public static function __autoinit()
  {
    static::$paths = array(
      \Themer::$PWD.'themer/',
      THEMER_BASEPATH.'themer/data/',
    );
    
    self::load('tumblr');
    self::load('defaults');
  }
  
  /**
   * Load a Themer yaml file
   *
   * @static
   * @access  public
   * @param   string  the name of the yaml file to load
   * @param   bool    whether or not to use the file name as an array index
   * @return  array   the config data, so it's there if you want
   */
  public static function load($file, $index = NULL)
  {
    $config = array();
    
    foreach(static::$paths as $p)
    {
      $path = $p.$file.YML;
      
      if(($tmp = self::load_file($path)) !== FALSE)
      {
        if(empty($index))
        {
          static::$config[$file] = $tmp;
        }
        else
        {
            static::$config[$index] = $tmp;
        }
        
        return $tmp;
      }
    }
  }
  
  /**
   * Returns the value of the specified 'config' item
   *
   * @static  
   * @access  public
   * @param   string  the item to fetch
   * @return  string  the value of the item or 'Item not found for: $item'
   */
  public static function get($item)
  { 
    $key = $item;
    $subitem = '';
    $subsubitem = '';
    
    if($pos = strpos($key, '.') > 0)
    {
      $items = explode('.', $item);
      
      $key = $items[0];
      $subitem = $items[1];
      
      if(count($items) > 2)
      {
        $subitem = $items[1];
        $subsubitem = $items[2];
      }
    }
    
    if(isset(static::$config[$key]))
    {
      if( ! empty($subitem))
      {
        if( ! empty($subsubitem))
        {
          if(isset(static::$config[$key][$subitem][$subsubitem]))
          {
            return static::$config[$key][$subitem][$subsubitem]; 
          }
        }
        elseif(isset(static::$config[$key][$subitem]))
        {
          return static::$config[$key][$subitem];
        }
      }
      else
      {
        return static::$config[$key];
      }
    }
    
    return FALSE;
  }
  
  /**
   * Finds data based on certain params
   * 
   * @static
   * @access  public
   * @param   string  the config item to look for
   * @param   array   the params to match
   * @return  array   an empty array or the matched data
   */
  public static function find($item, $params = array())
  {
    if( ! ($data = self::get($item)))
    {
      return array();
    }
    
    if(empty($params))
    {
      return $data;
    }
    
    $new = array();
    
    foreach($data as $d)
    {
      $found = FALSE;
      
      foreach($params as $k => $v)
      {
        $found = ( ! isset($d[$k]) || $d[$k] !== $v) ? FALSE : TRUE;
        if($found == FALSE) break;
      }
      
      if($found) $new[] = $d;
    }
    
    return $new;
  }
  
  /**
   * Write a PHP array or YAML string to a file
   * 
   * @static
   * @access  public
   * @param   mixed   the PHP array or YAML string to write
   * @param   string  the file name to use
   * @param   bool    are we writing to the PWD or HOME directory
   * @return  bool    whether or not the file was able to be written
   */
  public static function write($data, $name, $local = TRUE)
  { 
    $file_name = $name.YML;
    $data = (is_array($data)) ? self::to_yaml($data) : $data;
    
    $dir = Themer::$PWD."themer/";
    
    if( ! file_exists($dir))
    {
      if( ! @mkdir($dir, 0755))
      {
        Error::display("could not create directory: $dir", 500);
      }
    }
    
    $file_path = $dir.$name.YML;
    
    if( ! ($fh = @fopen($file_path, 'w')))
    {
      Error::display("cannot open file '".$name.YML."' at path $path");
    }
    
    $result = fwrite($fh, $data);
    fclose($fh);
  
    return ($result !== FALSE);
  }
  
  /**
   * Converts an array to a string representing a valid YAML <file></file>
   * 
   * @static
   * @access  public
   * @param   array   an array of data to dump
   * @return  string  the YAML representation of the data
   */
  public static function to_yaml($data)
  {
    $yaml = \sfYaml::dump($data, 5);
    return $yaml;
  }
  
  /**
   * Load a YAML config file
   * 
   * @static
   * @access  public
   * @param   string  the path to the YAML file
   * @return  array   the YAML file data as an array
   */
  public static function load_file($path)
  {
    if( ! file_exists($path)) return FALSE;
    
    $data = \sfYaml::load($path);
    
    if(empty($data)) return FALSE;
    
    return $data;
  }
}

/* End of file data.php */
/* Location: ./themer/data.php */