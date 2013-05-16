<?php
/**
 * JQuarks Component Answers View
 * 
 * @version		$Id: view.html.php 59 2010-03-02 08:40:27Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Views
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

jimport('joomla.application.component.view');


class JQuarksViewSessions extends JView 
{
	function display($tpl = null)
	{
		$user     =& JFactory::getUser();
		$sessions =& $this->get( 'sessions' );
		$lists    =& $this->get( 'lists' ) ;
		$pageNav  =& $this->get( 'pageNav' ) ;

		$this->assignRef('sessions', $sessions );
		$this->assignRef('lists', $lists ) ;
		$this->assignRef('pageNav', $pageNav) ;
		$this->assignRef('user', $user) ;
		
		// building the submenu
        JSubMenuHelper::addEntry( JText::_( 'JQUARKS_HOME' ), 'index.php?option=com_jquarks');
        JSubMenuHelper::addEntry( JText::_( 'QUIZZES' ), 'index.php?option=com_jquarks&view=quizzes');
        JSubMenuHelper::addEntry( JText::_( 'SETS_OF_QUESTIONS'), 'index.php?option=com_jquarks&view=setsofquestions');
        JSubMenuHelper::addEntry( JText::_( 'QUESTIONS' ), 'index.php?option=com_jquarks&view=questions');
        JSubMenuHelper::addEntry( JText::_( 'CATEGORIES' ), 'index.php?option=com_jquarks&view=categories');
        JSubMenuHelper::addEntry( JText::_( 'SESSIONS' ), 'index.php?option=com_jquarks&view=sessions', true);
        JSubMenuHelper::addEntry( JText::_( 'CANDIDATES' ), 'index.php?option=com_jquarks&view=candidates');
        JSubMenuHelper::addEntry( JText::_( 'PROFILES' ), 'index.php?option=com_jquarks&view=profiles');
		
		JToolBarHelper::title( JText::_( 'SESSIONS' ), 'generic.png' );
		
		$toolbar =& JToolBar::getInstance() ;
		//$toolbar->appendButton("clearUnfinished", "clearUnfinished.png", JText::_('CLEAR_UNFINISHED'));
		
		if ($user->usertype == "Super Administrator" ) {
			JToolBarHelper::deleteList(JText::_('CONFIRM_SUPPRESSION_ANSWER'));
		}
		
		JToolBarHelper::help( 'sessions', true);
		
		parent::display($tpl);	
	}
	
}