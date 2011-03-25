<?php

namespace Themer;

class Error {
  
  /**
   * A PHP error handler for Themer
   * 
   * @static
   * @access  public
   * @param   int     the error level
   * @param   string  the error message
   * @param   string  the file path
   * @param   int     the line number
   * @return  void
   */
  public static function php_error($level, $msg, $file, $line)
  {
    $message = "$msg on line $line in $file";
    self::display($message, 500);
  }
  
  /**
   * Display a Themer error.
   * 
   * @static
   * @access  public
   * @param   string  the error message
   * @param   int     the error code
   * @return  void
   */
  public static function display($message, $code = 404)
  {
    $codes = array(
      404 => 'Page Not Found',
      500 => 'Internal Themer Error'
    );
    
    $title = isset($codes[$code]) ? $codes[$code] : $codes[500];
    
    echo View::load('error', array('title' => $title, 'message' => $message), TRUE);
    exit();
  }
  
}

/* End of file error.php */
/* Location: ./themer/error.php */