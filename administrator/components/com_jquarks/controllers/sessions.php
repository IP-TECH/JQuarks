<?php
/**
 * JQuarks Component Answers Controller
 * 
 * @version		$Id: sessions.php 59 2010-03-02 08:40:27Z fnaccache $
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


class JQuarksControllerSessions extends JQuarksController 
{
	function __construct() 
	{
		parent::__construct();
	}
	
	function clearUnfinished() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );		
		
		$model = $this->getModel('sessions');
		
		if ($model->clearUnfinished()) 
		{ 
			$msg = JText::_( 'UNFINISHED_SESSION_CLEARED' ) ;
			$type = "message" ;
		} 
		else 
		{
			$msg = JText::_( 'ERROR_CLEARING_SESSIONS' ) ;
			$type = "error" ;
		}
		
		$link = 'index.php?option=com_jquarks&view=sessions' ;
		$this->setRedirect($link, $msg, $type) ;
	}
	
	function exportCSV() 
	{
		$model = $this->getModel('sessions');
		
		$model->exportCSV() ;
			
		$link = 'index.php?option=com_jquarks&view=sessions' ;
		$this->setRedirect($link) ;
	}
	
	function remove() 
	{
		$this->_user =& JFactory::getUser();
		if ($this->_user->usertype != "Super Administrator" ) {
			jexit('Restricted Access') ;
		}
			
		JRequest::checkToken() or jexit( 'Invalid Token' );		
		
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;
		
		$model = $this->getModel('sessions') ;
		
		if ($model->delete($cids)) 
		{
			$msg = JText::_( 'SESSION(S)_DELETED' ) ;
			$type = "message" ;
		} 
		else 
		{
			$msg = JText::_( 'ERROR_DELETING_SESSION(S)' ) ;
			$type = "error" ;
		}
		
		$link = 'index.php?option=com_jquarks&view=sessions' ;
		$this->setRedirect($link, $msg, $type) ;
	}
	
    /**
     * Set the input-type answer of a user to correct
     * 
     * @return void
     */
    function correct() 
    {
        JRequest::checkToken() or jexit( 'Invalid Token' );
        
        $model = $this->getModel('session');
        
        $array = JRequest::getVar('cid', 0, '', 'array') ;
        $id = (int)$array[0] ;

        if ($model->correct(1)) 
        {
            $msg = JText::_( 'ANSWER_MARKED_CORRECT' ) ;
            $type = "message" ;
        } 
        else 
        {
            $msg = JText::_( 'ERROR_EVALUATING_ANSWER' ) ;
            $type = "error" ;
        }
        
        $link = 'index.php?option=com_jquarks&view=session&cid[]=' . $id ;
        $this->setRedirect($link, $msg, $type) ;
    }
    
    /**
     * Set the input-type answer of a user to incorrect
     * 
     * @return void
     */
    function incorrect() 
    {
        JRequest::checkToken() or jexit( 'Invalid Token' );     
        
        $model = $this->getModel('session');
        
        $array = JRequest::getVar('cid', 0, '', 'array') ;
        $id = (int)$array[0] ;
        
        if ($model->correct(0)) 
        {
            $msg = JText::_( 'ANSWER_MARKED_INCORRECT' ) ;
            $type = "message" ;
        } 
        else 
        {
            $msg = JText::_( 'ERROR_EVALUATING_ANSWER' ) ;
            $type = "error" ;
        }
        
        $link = 'index.php?option=com_jquarks&view=session&cid[]=' . $id ;
        $this->setRedirect($link, $msg, $type) ;
    }
    
    /**
     * Set the input-type answer of a user to correct
     * 
     * @return void
     */
    function unAnswered() 
    {
        JRequest::checkToken() or jexit( 'Invalid Token' );

        $model = $this->getModel('session');
        
        $array = JRequest::getVar('cid', 0, '', 'array') ;
        $id = (int)$array[0] ;

        if ($model->correct(-2)) 
        {
            $msg = JText::_( 'ANSWER_MARKED_UNANSWERED' ) ;
            $type = "message" ;
        } 
        else 
        {
            $msg = JText::_( 'ERROR_EVALUATING_ANSWER' ) ;
            $type = "error" ;
        }
        
        $link = 'index.php?option=com_jquarks&view=session&cid[]=' . $id ;
        $this->setRedirect($link, $msg, $type) ;
    }
    
    function cancel() 
    {
    	JRequest::checkToken() or jexit( 'Invalid Token' );
    	
        $this->setRedirect( 'index.php?option=com_jquarks&view=sessions' ) ;
    }
	
}