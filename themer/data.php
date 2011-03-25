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
        THEMER_BASEPATH.'themer/data/',
        \Themer::$home.'.themer/',
        \Themer::$pwd.'themer/',
      );
    }
    else
    {
      static::$paths = array(
        THEMER_BASEPATH.'themer/data/',
        \Themer::$pwd.'themer/',
      );
    }
  
    static::$config = array();
    
    self::load('tumblr');
    self::load('blog', TRUE);
  }
  
  /**
   * Load a Themer yaml file
   *
   * @static
   * @access  public
   * @param   string  the name of the yaml file to load
   * @param   bool    whether or not to replace the existing data, if there is any
   * @return  array   the config data, so it's there if you want
   */
  public static function load($file, $replace = FALSE)
  {
    $config = array();
    
    foreach(static::$paths as $p)
    {
      $path = $p.$file.self::YAML_EXT;
      
      if(($tmp = self::load_file($path)) !== FALSE)
      {
        
        if(empty($config) || $replace)
        {
          $config = $tmp;
        }
        else
        {
          array_merge($config, $tmp);
        }
      }
    }
    
    if( ! empty($config))
    {
      static::$config[$file] = $config;
      return $config;
    }
    
    return NULL;
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
    
    try
    {
      if( ! empty($subitem))
      {
        if( ! empty($subsubitem))
        {
          return static::$config[$key][$subitem][$subsubitem];
        }
      
        return static::$config[$key][$subitem];
      }
    
      return static::$config[$key];
    }
    catch(\Exception $e)
    {
      return 'Item not found: '.$item;
    }
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