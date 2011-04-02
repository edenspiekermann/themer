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

namespace Themer\Parser;

use Themer\Parser\Variable;
use Themer\Parser\Block;

require THEMER_BASEPATH.'themer/third_party/simple_html_dom.php';

/**
 * Themer Meta Class 
 *
 * Loads the custom Tumblr meta variables from the theme.
 *
 * @package    Themer
 * @subpackage Parser
 * @author     Braden Schaeffer 
 */
class Meta {
  
  public $metas = array(
    'color' => array(),
    'font'  => array(),
    'if'    => array(),
    'text'  => array(),
    'image' => array()
  );
  
  protected $defaults = array(
    'AskEnabled' => FALSE,
  );
  
  public $theme = '';
  public $dom = NULL;
  
  /**
   * Constructor that initializes a simple_html_dom class using the
   * passed contents of a theme.
   * 
   * @access  public
   * @param   string  the theme contents
   * @return  void 
   */
  public function __construct($theme = '')
  {
    $this->theme = $theme;
    
    $this->dom = new \simple_html_dom;
    $this->dom->load($this->theme);
      
    $this->parse_metas();
    
    if(isset($_GET['meta']))
    {
      foreach($this->metas as $key => $ignore)
      {
        if(isset($_GET['meta'][$key]))
        {
          $this->metas[$key] = $_GET['meta'][$key];
        }
      }
    }
  }
  
  /**
   * Simple wrapper for loading and returning parsed theme variable meta
   * data.
   * 
   * @static
   * @access  public
   * @param   string  the theme contents
   * @return  array   the custom theme variable meta data
   */
  public static function load($theme = '')
  {
    $obj = new self($theme);
    return $obj->metas;
  }
  
  /**
   * Renders meta data in a theme
   *
   * @access  public
   * @param   string  a theme with unparsed meta_data
   * @return  string  the theme with meta data parsed out
   */
  public static function render($theme)
  {
    $obj = new self($theme);
    
    foreach($obj->metas as $meta => $data)
    {
      foreach($data as $tag => $value)
      {
        $tag = str_replace("_", " ", $tag);
        $obj->_render_tag($meta, $tag, $value);
      }
    }
    
    return $obj->theme;
  }
  
  /**
   * Uses the simple_html_dom object to find custom Tumblr meta tags in
   * the theme file.
   * 
   * @access  private
   * @return  void
   */
  private function parse_metas()
  {
    $elements = $this->dom->find("meta[name*=:]");
  
    foreach($elements as $el)
    {
      if(isset($el->content))
      {
        $attr = explode(':', $el->name);
        
        if(array_key_exists($attr[0], $this->metas))
        {
          // For variables like 'text:Flickr ID', replace ' ' with '_' so we
          // can use it in the a form as a name attribute for in input in a form
          // like so: <input type="text" name="meta[text][Flickr_ID]" />
          
          $key = str_replace(" ", "_", $attr[1]);
          $this->metas[$attr[0]][$key] = $el->content;
        }
      }
    }
  }
  
  /**
   * Render each meta tag
   * 
   * @access  private
   * @param   string  the type of meta tag
   * @param   string  the name of meta tag
   * @param   string  the value of the meta tag
   * @return  void
   */
  private function _render_tag($meta, $tag, $value)
  {
    switch($meta)
    {
      case 'if':
        $render = ($value == '1') ? TRUE : FALSE;
        $this->theme = Block::render_if($this->theme, $tag, $render, FALSE);
        break;
        
      case 'text':
        $this->_render_text($tag, $value);
        break;
        
      default:
        $this->theme = Variable::render($this->theme, $meta.":".$tag, $value, FALSE);
        break;
    }
  }
  
  /**
   * Render a text tag
   * 
   * @access  private
   * @param   string  the name of the text tag
   * @param   string  the actual text
   * @return  void
   */
  private function _render_text($tag, $value)
  {
    $if_value = (empty($value)) ? FALSE : TRUE;
    $this->theme = Block::render_if($this->theme, $tag, $if_value);
    $this->theme = Variable::render($this->theme, 'text:'.$tag, $value, FALSE);
  }
}

/* End of file meta.php */
/* Location: ./themer/parser/meta.php */