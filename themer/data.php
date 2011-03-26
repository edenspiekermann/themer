<?php

namespace Themer;

require 'SymfonyComponents/YAML/sfYaml.php';

class Data {
  
  const YAML_EXT = '.yml';
  
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
    if( ! empty(\Themer::$home))
    {
      static::$paths = array(
        \Themer::$pwd.'themer/',
        \Themer::$home.'.themer/',
        THEMER_BASEPATH.'themer/data/',
      );
    }
    else
    {
      static::$paths = array(
        \Themer::$pwd.'themer/',
        THEMER_BASEPATH.'themer/data/',
      );
    }
  
    static::$config = array();
    
    self::load('tumblr');
    self::load('data');
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
      $path = $p.$file.self::YAML_EXT;
      
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
    if( ! $local && empty(\Themer::$home))
    {
      Error::display("cannot locate \$HOME directory within the current environment", 500);
    }
    
    $file_name = $name.self::YAML_EXT;
    $data = (is_array($data)) ? self::to_yaml($data) : $data;
    
    $dir = ($local) ? \Themer::$pwd."themer/" : \Themer::$home.".themer/";
    
    if( ! file_exists($dir))
    {
      if( ! @mkdir($dir, 0755))
      {
        Error::display("could not create directory: $dir", 500);
      }
    }
    
    $file_path = $dir.$name.self::YAML_EXT;
    
    if( ! ($fh = @fopen($file_path, 'w')))
    {
      Error::display("cannot open file '".$name.self::YAML_EXT."' at path $path");
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