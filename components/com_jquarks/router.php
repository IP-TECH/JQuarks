<?php
/** 
 * Make the url to the quiz SEF
 * 
 * @version		$Id: router.php 59 2010-03-02 08:40:27Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package 	JQuarks-Front-Office
 * @link		http://www.iptechinside.com/labs/projects/show/jquarks
 * @since 		0.1
 * @license     GNU/GPL2
 *    
 *    This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; version 2
 *  of the License.
 *
 *    This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA. 
 *  or see <http://www.gnu.org/licenses/>
 */

defined('_JEXEC') or die('Restricted Access') ;

/**
 * remove from the query the name of the controller and the id and build a segment
 * @param $query
 * @return array
 */
function JQuarksBuildRoute (&$query) 
{
	$segments = array() ;
	
	if (array_key_exists('view', $query)) 
	{
		$segments[] = $query['view'];
		unset($query['view']) ;
	}

	if (array_key_exists('id', $query)) 
	{
		$segments[] = $query['id'] ;
		unset($query['id']) ;
	}
	
	return $segments ;
}

/**
 * Parse the segment to fetch the name of the controller and the id of the quiz
 * @param $segments
 * @return array
 */
function JQuarksParseRoute ($segments) 
{
	$vars = array() ;
	$vars['view'] = $segments[0] ;
		
	if (array_key_exists(1, $segments)) {
		$vars['id'] = $segments[1];
	}
	
	return $vars ;
}