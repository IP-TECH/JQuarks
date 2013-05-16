<?php
/**
 * JQuarks Component Answer Controller
 * 
 * @version		$Id: session.php 90 2010-12-29 14:07:36Z fnaccache $
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


class JQuarksControllerSession extends JQuarksController 
{
	public $_model ;
	
	function __construct() 
	{
		parent::__construct();

		$this->_model = $this->getModel('session') ;
		
		$this->registerTask( 'correct',	'correct' );
		$this->registerTask( 'incorrect', 'incorrect' );
	}
	
	function display() 
	{
            $view = & $this->getView( 'session', 'html' );
            $view->setModel( $this->getModel( 'question' ) );
            
            JRequest::setVar('view','session') ;
            parent::display();
	}
	
	/**
	 *  set the input-type answer of a user to correct
	 * @return void
	 */
	function correct() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$array = JRequest::getVar('cid', 0, '', 'array') ;
	 	$id = (int)$array[0] ;

		if ($this->_model->correct(1)) 
		{
			$msg = JText::_( 'ANSWER_MARKED_CORRECT' ) ;
			$type = "message" ;
		} 
		else 
		{
			$msg = JText::_( 'ERROR_EVALUATING_ANSWER' ) ;
			$type = "error" ;
		}
		
		$link = 'index.php?option=com_jquarks&controller=session&cid[]=' . $id ;
		$this->setRedirect($link, $msg, $type) ;
	}
	
	/**
	 *  set the input-type answer of a user to incorrect
	 * @return void
	 */
	function incorrect() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );		
		
		$array = JRequest::getVar('cid', 0, '', 'array') ;
	 	$id = (int)$array[0] ;
		
		if ($this->_model->correct(0)) 
		{
			$msg = JText::_( 'ANSWER_MARKED_INCORRECT' ) ;
			$type = "message" ;
		} 
		else 
		{
			$msg = JText::_( 'ERROR_EVALUATING_ANSWER' ) ;
			$type = "error" ;
		}
		
		$link = 'index.php?option=com_jquarks&controller=session&cid[]=' . $id ;
		$this->setRedirect($link, $msg, $type) ;
	}
	
	/**
	 *  set the input-type answer of a user to correct
	 * @return void
	 */
	function unAnswered() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$array = JRequest::getVar('cid', 0, '', 'array') ;
	 	$id = (int)$array[0] ;

		if ($this->_model->correct(-2)) 
		{
			$msg = JText::_( 'ANSWER_MARKED_UNANSWERED' ) ;
			$type = "message" ;
		} 
		else 
		{
			$msg = JText::_( 'ERROR_EVALUATING_ANSWER' ) ;
			$type = "error" ;
		}
		
		$link = 'index.php?option=com_jquarks&controller=session&cid[]=' . $id ;
		$this->setRedirect($link, $msg, $type) ;
	}
	
	function cancel() 
	{
		$this->setRedirect( 'index.php?option=com_jquarks&controller=sessions' ) ;
	}	
	
}