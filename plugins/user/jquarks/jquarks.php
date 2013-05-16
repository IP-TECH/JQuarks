<?php
/**
 * JQuarks User Plugin
 * 
 * @version		$Id: jquarks.php 59 2010-03-02 08:40:27Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package		JQuarks-Plugin
 * @subpackage	User
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgUserJquarks extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param 	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgUserJquarks(& $subject, $config) 
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Remove all affectation to quizzes for the deleted user
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param 	array	  	holds the user data
	 * @param	boolean		true if user was succesfully stored in the database
	 * @param	string		message
	 */
	function onUserAfterDelete($user, $success, $msg)
	{
		if ($success) 
		{
			$db = &JFactory::getDBO();

			// getting the assignations for this user
			$query = 'SELECT id' .
			' FROM #__jquarks_users_quizzes' . 
			' WHERE user_id = ' . $user['id'] ;
		
			$db->setQuery($query);
			$assignations = $db->loadResultArray();
			
			if (count($assignations)) 
			{
				//$affectationList = implode(',', $affectations) ;
				
				foreach ($assignations AS $assignation) 
				{
					// getting the number of sessions
					$query = 'SELECT count(id)' .
					' FROM #__jquarks_quizsession' .
					' WHERE affected_id = ' . $assignation ;
					
					$db->setQuery($query) ; $db->getQuery(); //exit();
					
					if ($db->loadResult()) 
					{
						// there are session, so we archieve the assignations
						//$sessionList = implode(',', $sessions) ;
						
						$query = 'UPDATE #__jquarks_users_quizzes' .
						' SET archived = 1' .
						' WHERE id = ' . $assignation ;
						
                                                $db->setQuery($query);
                                                $db->loadResult();
					} 
					else 
					{
						// no sessions are found we may delete the assignations
						$query = 'DELETE FROM #__jquarks_users_quizzes' .
						' WHERE id = ' . $assignation ;
						$db->setQuery($query);
						$db->loadResult();
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Add the newly created user to all public quizzes already existant
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param 	array	  	holds the user data
	 * @param   boolean		true if user is new
	 * @param	boolean		true if user was succesfully stored in the database
	 * @param	string		message
	 */
	function onUserAfterSave($user, $isnew, $success, $msg)
	{
		if ($isnew && $success) 
		{
			// case of a new user to affect to public quizzes
			$db = &JFactory::getDBO();
			
			//getting all public quizzes
			$query = 'SELECT id' .
			' FROM #__jquarks_quizzes' . 
			' WHERE access_id = 0' ;
			
			$db->setQuery($query); 
			$publicQuizzes = $db->loadResultArray();
 
			if (count($publicQuizzes)) 
			{
				foreach ($publicQuizzes AS $publicQuiz) 
				{
					$query = 'INSERT INTO' .
					' #__jquarks_users_quizzes' .
					' (`quiz_id`, `user_id`)' .
					' VALUES (' . $publicQuiz . ', ' . $user['id'] . ')' ;
                                        $db->setQuery($query);
                                        $db->loadResult();
				}
			}
		}
		
		return true;
	}

    /**
     * redirect logged in user to quizzes view
     * this function prevents unvalid URL after login
     *
     * @global JApplication $mainframe
     * @param  array $user
     * @param  array $options
     * @return boolean
     */
	function onUserLogin($user, $options)
	{
        $mainframe =& JFactory::getApplication();

        $u =& JURI::getInstance();

        if($u->getVar("option") == 'com_jquarks') {
          $mainframe->redirect('index.php?option=com_jquarks&view=quizzes');
        }
		return true;
	}

    /**
     *redirect logged out user to quizzes view
     * this function prevents unvalid URL after logout
     *
     * @global JApplication $mainframe
     * @param array $user
     * @return boolean
     */
    function onUserLogout($user)
	{
        $mainframe =& JFactory::getApplication();

        $u =& JURI::getInstance();

        if($u->getVar("option") == 'com_jquarks') {
          $mainframe->redirect('index.php?option=com_jquarks&view=quizzes');
        }
		return true;
    }

}