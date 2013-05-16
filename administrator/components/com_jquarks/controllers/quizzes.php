<?php
/**
 * JQuarks Component Quizzes Controller
 * 
 * @version		$Id: quizzes.php 69 2010-04-23 07:52:57Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Controllers
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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');


class JQuarksControllerQuizzes extends JQuarksController 
{
	public $_model ;
	
	function __construct() 
	{
		parent::__construct();
		
		$this->_model = $this->getModel('quizzes') ;
		
		// register extra tasks
		$this->registerTask( 'add', 'edit' ) ;
		$this->registerTask( 'publish', 'publish') ;
	}
		
	function display() 
	{		
		JRequest::setVar('view','quizzes') ;
		parent::display() ;
	}
	
	function edit() 
	{
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;
		
		$link = 'index.php?option=com_jquarks&controller=quiz&cid[]=' . $cids[0] ;
		$this->setRedirect($link) ;
	}
	
	/**
	 * Publish the selected quiz
	 * 
	 * @return void
	 */
	function publish() 
	{
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;

        if ($this->_model->publish($cids[0]))
		{
			$msg = JText::_( 'QUIZ_PUBLISHED' ) ;
			$type = 'message' ;
		} 
		else 
		{
			$msg = JText::_( 'ERROR_QUIZ_PUBLICATION_FAILED' ) ;
			$type = 'error' ;			
		} 
		
		$link = 'index.php?option=com_jquarks&controller=quizzes&task=display' ;
		$this->setRedirect($link, $msg, $type) ;		
	}
	
	/**
	 * Unpublish the selected quiz
	 * 
	 * @return void
	 */
	function unpublish() 
	{
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;
		
		if ($this->_model->unPublish($cids[0])) 
		{
			$msg = JText::_( 'QUIZ_UNPUBLISHED' ) ;
			$type = 'message' ;
		} 
		else 
		{
			$msg = JText::_( 'ERROR_QUIZ_UNPUBLICATION_FAILED' ) ;	
			$type = 'error' ;					
		} 
		
		$link = 'index.php?option=com_jquarks&controller=quizzes&task=display' ;
		$this->setRedirect($link, $msg, $type) ;		
	}
	
	/**
	 * Redirect to assignUser page
	 * 
	 * @return void
	 */
	function assignUsers() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;
		
		$link = 'index.php?option=com_jquarks&controller=quiz&layout=assignUsers&cid[]=' . $cids[0] ;
		$this->setRedirect($link) ;
	}
	
	/**
	 * Redirect to assignSets page
	 * 
	 * @return void
	 */
	function assignSets() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;
		
		$link = 'index.php?option=com_jquarks&controller=quiz&layout=assignSets&cid[]=' . $cids[0] ;
		$this->setRedirect($link) ;
	}
	
	function remove() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;
		
		if ($this->_model->delete($cids)) 
		{
			$msg = JText::_( 'QUIZ(ZES)_DELETED' ) ;
			$type = 'message' ;
		} 
		else 
		{
			$msg = JText::_( 'ERROR_DELETING_QUIZ(ZES)' ) ;
			$type = 'error' ;
		}
		
		$link = 'index.php?option=com_jquarks&controller=quizzes' ;
		$this->setRedirect($link, $msg, $type) ;
	}
	
}