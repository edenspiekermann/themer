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

namespace Themer;

use Themer\Parser\Meta;
use Themer\Parser\Posts;
use Themer\Parser\Block;
use Themer\Parser\Variable;

/**
 * Themer Parser Class 
 *
 * Parses Tumblr theme files.
 *
 * @package     Themer
 * @subpackage  Parser
 * @author      Braden Schaeffer <braden.schaeffer@gmail.com>
 */
class Parser {
  
	protected static $pages = array(
	 'Index'      => array('Index'),
 	 'Permalink'  => array('Permalink'),
	 'Search'     => array('Search', 'Index'),
	 'Tag'        => array('Tag', 'Index')
	);
	
	/**
	 * Parses the Tumblr theme
	 *
	 * @access  public
	 * @param   string  the theme contents to parse
	 * @return  string  the parsed theme
	 */
	public static function parse($theme = '', $page = 'Index')
	{
		if(empty($theme)) return '';

		$theme = Meta::render($theme);
		$theme = Posts::render($theme);
		
		return $theme;
	}
}

/* End of file parser.php */
/* Location: ./themer/parser.php */