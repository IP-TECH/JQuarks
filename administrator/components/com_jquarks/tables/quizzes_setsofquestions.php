<?php
/**
 * JQuarks Component quizzes_setsofquestions Table
 * 
 * @version		$Id: quizzes_setsofquestions.php 59 2010-03-02 08:40:27Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Tables
 * @link		http://www.iptechinside.com/labs/projects/show/jquarks
 * @since		0.1
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

defined('_JEXEC') or die('Restricted access') ;


class Tablequizzes_setsofquestions extends JTable 
{
	/**
	 * 
	 * @var int
	 */
	public $id = null ;
	
	/**
	 * 
	 * @var int
	 */
	public $quiz_id = null ;
	
	/**
	 * 
	 * @var int
	 */
	public $setofquestions_id = null ;
		
	
	function Tablequizzes_setsofquestions(& $db) 
	{
		parent::__construct('#__jquarks_quizzes_setsofquestions', 'id', $db) ;
	}
	
}