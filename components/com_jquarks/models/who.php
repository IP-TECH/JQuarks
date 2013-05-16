<?php
/**
 * JQuarks Component Who Model
 * 
 * @version		$Id: who.php 59 2010-03-02 08:40:27Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package 	JQuarks-Front-Office
 * @subpackage	Models
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

defined('_JEXEC') or die();

jimport('joomla.application.component.model');


class JQuarksModelWho extends JModel 
{
	function __construct() 
	{
		parent::__construct();
				
		$this->_sessionId = (int) JRequest::getVar('sessionId') ; 
		
		$this->sessionWhoTable =& JTable::getInstance('sessionwho', 'Table') ;
	}
	
	/**
	 * Store informations about session's user
	 * 
	 * @return boolean
	 */
	function store() 
	{
		$query = 'SELECT * ' .
		' FROM #__jquarks_sessionwho' .
		' WHERE session_id = ' . $this->_sessionId ;
		
		$this->_db->setQuery($query) ;
		$lineWho['id'] = $this->_db->loadResult() ;
		
		// getting the session's id
		$lineWho['session_id'] = $this->_sessionId ;
		$lineWho['givenname'] = JRequest::getVar('firstname') ;
		$lineWho['familyname'] = JRequest::getVar('lastname') ;
		$lineWho['email'] = JRequest::getVar('email') ;

		return $this->sessionWhoTable->save($lineWho) ;
	}
	
}