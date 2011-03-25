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

namespace Themer\Parser;
use Themer\Parser as Parser;

/**
 * Themer Language Class 
 *
 * Parses Tumblr's {lang:Item} variables
 *
 * @package     Themer
 * @subpackage  Parser
 * @author      Braden Schaeffer <braden.schaeffer@gmail.com>
 */
class Lang {
  
  const MATCHER = '/{lang:([A-Z][A-Za-z0-9-\s]*)}/';
  
  /**
   * Parses Tumblr's 
   * 
   * @static
   * @access  public
   * @return  void
   */
  public static function parse()
  {
    
  }
}

/* End of file lang.php */
/* Location: ./themer/parser/lang.php */