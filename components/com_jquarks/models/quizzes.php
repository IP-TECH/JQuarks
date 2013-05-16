<?php
/**
 * JQuarks Component Quizzes Model
 * 
 * @version		$Id: quizzes.php 59 2010-03-02 08:40:27Z fnaccache $
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


class JQuarksModelQuizzes extends JModel 
{
	function __construct() 
	{
		parent::__construct();		
	}
	
	/**
	 * Get the list of the public Quizzes
	 * 
	 * @return array
	 */
	function getPublicQuizzes() 
	{
		$config =& JFactory::getConfig();
		$jnow		=& JFactory::getDate();
		$jnow->setOffset( $config->getValue('config.offset' ));
		$now		= $jnow->toMySQL(true);
		
		$query = 'SELECT qui.*' .
		' FROM #__jquarks_quizzes AS qui' .
		' WHERE qui.published = 1' .
		' AND qui.access_id = 0' .
		' AND qui.publish_up < \'' . $now . '\'' .
		' AND ( qui.publish_down = "0000-00-00 00:00:00" OR qui.publish_down > "' . $now . '" )' ;
		
		$this->_publicQuizzes = $this->_getList($query);
		if($this->_db->getErrorNum()) {
			return false ;
		}
		
		return $this->_publicQuizzes ;
	}
	
	/**
	 * Get the list of the private Quizzes for the current logged in user
	 * 
	 * @return array
	 */
	function getUserQuizzes() 
	{
		$user =& JFactory::getUser();
		
		$config =& JFactory::getConfig();
		$jnow		=& JFactory::getDate();
		$jnow->setOffset( $config->getValue('config.offset' ));
		$now		= $jnow->toMySQL(true);

		$query = 'SELECT qui.*' .
		' FROM #__jquarks_quizzes AS qui' .
		' LEFT JOIN #__jquarks_users_quizzes as users_quizzes ON qui.id = users_quizzes.quiz_id' .
		' WHERE qui.published = 1' .
		' AND qui.access_id = 1' .
		' AND users_quizzes.user_id = ' . $user->id . 
		' AND users_quizzes.archived <> 1' .
		' AND qui.publish_up < \'' . $now . '\'' .
		' AND ( qui.publish_down > \'' . $now . '\' OR qui.publish_down = \'0000-00-00 00:00:00\' )' ;
		
		$this->_userQuizzes = $this->_getList($query);
		if($this->_db->getErrorNum()) {
			return false ;
		}
		
		return $this->_userQuizzes ;
	}
	
}