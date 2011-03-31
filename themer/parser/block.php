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

/**
 * Themer Block Class 
 *
 * Captures and renders Tumblr tag blocks
 *
 * @package     Themer
 * @subpackage  Parser
 * @author      Braden Schaeffer 
 */
class Block {
  
  /**
   * Matches all blocks in a given string
   * 
   * Taken from Thimble by Mark Wunsch -- github.com/mwunsch/thimble
   *
   * @access  public
   * @var     string
   */
  const MATCHER = '/{block:([A-Za-z][A-Za-z0-9]*)}(.*?){\/block:\\1}/is';
  
  /**
   * Strips the open and close tags from a matched block.
   * 
   * Taken from Thimble by Mark Wunsch -- github.com/mwunsch/thimble
   * 
   * @static
   * @access  public
   * @param   string  the block to search in
   * @param   string  the block tag to strip
   * @return  string  the stripped block
   */
  public static function render($block, $tag)
  {
    return preg_replace_callback(
			self::_matcher($tag),
			create_function('$matches', 'return $matches[2];'),
			$block
		);
  }
  
  /**
   * Renders if blocks
   * 
   * @static
   * @access  public
   * @param   string  the block to search in
   * @param   string  the block tag name
   * @param   bool    whether or not we should render the block
   * @return  string  the stripped block
   */
  public static function render_if($block, $tag, $render = FALSE)
  {
    $tag = self::_tag_for_if_block($tag);
    
    if($render === TRUE)
    {
      $block = self::remove($block, 'IfNot'.$tag);
      $block = self::render($block, 'If'.$tag);
    }
    else
    {
      $block = self::remove($block, 'If'.$tag);
      $block = self::render($block, 'IfNot'.$tag);
    }
    
    return $block;
  }
  
  /**
   * Removes all other block tags from a given block
   * 
   * Taken from Thimble by Mark Wunsch -- github.com/mwunsch/thimble
   * 
   * @static
   * @access  public
   * @param   string  the given block
   * @return  string  the cleaned up block
   */
  public static function cleanup($block)
  {
    return preg_replace(self::MATCHER, '', $block);
  }
  
  /**
   * Remove a block completely.
   * 
   * @static
   * @access  public
   * @param   string  the block
   * @param   string  the block tag to remove
   * @return  string  the cleaned up block
   */
  public static function remove($block, $tag)
  {
    $blocks = self::find($block, $tag);
    
    if( ! empty($blocks))
    {
      foreach($blocks as $b)
      {
        $block = str_replace($b, "", $block);
      }
    }
    
    return $block;
  }
  
  /**
   * Attempt to match a set of block open/close tags within a given
   * block.
   * 
   * @static
   * @access  public
   * @param   string  the parent block
   * @param   string  the tag to search for
   * @return  array   empty array for no matches, else the matches
   */
  public static function find($block, $tag)
  {
    if( ! preg_match_all(self::_matcher($tag), $block, $matches))
    {
      return array();
    }
    
    return $matches[0];
  }
  
  /**
   * Returns a formatted block matcher.
   * 
   * Taken from Thimble by Mark Wunsch -- github.com/mwunsch/thimble
   *
   * @static
   * @access  private
   * @param   string  the tag name to use
   * @return  string  the formated block pattern matcher
   */
  private static function _matcher($tag = '')
  {
    return '/{block:('.$tag.')}(.*?){\/block:\\1}/is';
  }
  
  /**
   * Formats a tag name for If and IfNot blocks. We have to reformat the
   * tag for the blocks (they can't be spaced and the _'s comes from Themer
   * setting form input names with _'s in the keys)
   * 
   * @access  public
   * @param   string  the tag name to format
   * @return  string  the formatted tag
   */
  private static function _tag_for_if_block($tag = '')
  {
    return str_replace(array(" ", "_"), "", $tag);
  }
}

/* End of file block.php */
/* Location: ./themer/parser/block.php */