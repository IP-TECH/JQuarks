<?php
/**
 * JQuarks Component Quiz Controller
 * 
 * @version		$Id: quiz.php 66 2010-03-08 10:45:32Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package 	JQuarks-Front-Office
 * @subpackage	Controllers
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


class JQuarksControllerQuiz extends JQuarksController 
{
	var $_model ;
	
	var $_questions ;


	function __construct() 
	{
		parent::__construct();
	}


	function display()
	{
		$quizId = JRequest::getInt('id') ;
		
		$model =& $this->getModel('quiz') ;
		
		$affectedId = $model->getAffectedId( $quizId );
                
        if ($affectedId) // checking access clearance to quiz
        {
            // checking if the quiz is a unique session one or if it's a timed
            $quiz = $model->getQuiz($quizId) ; 

            JRequest::setVar('view','quiz') ;
            JRequest::setVar('layout','_quizinfo') ;
            
            parent::display();
        }
	    else 
        {
            $this->_user =& JFactory::getUser();
            
            if ($this->_user->guest == 1) 
            {
                $msg = JText::_('YOU_ARE_NOT_GRANTED_ACCESS_PLEASE_SIGNIN') ;
                $type = 'message' ;
                $link =  JRoute::_('index.php?option=com_user&view=login', false) ;
            } 
            else 
            {
                $msg = JText::_('YOU_ARE_NOT_GRANTED_ACCESS_TO_THIS_QUIZ') ;
                $type = 'notice' ;
                $link =  JRoute::_('index.php?option='. $option . '&view=quizzes', false) ;
            }
                
            
            $this->setRedirect($link, $msg, $type) ;
        }
	}
	
	function showQuiz() 
	{	
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$quizId = JRequest::getVar('id') ;
		
		$model =& $this->getModel('quiz') ;
		
		if ($model->getAffectedId( $quizId )) // checking access clearance to quiz
		{
			JRequest::setVar('view','quiz') ;
			parent::display() ;
		} 
		else 
		{
			$this->_user =& JFactory::getUser();
			
			if ($this->_user->guest == 1) 
			{
				$msg = JText::_('YOU_ARE_NOT_GRANTED_ACCESS_PLEASE_SIGNIN') ;
				$type = 'message' ;
			} 
			else 
			{
				$msg = JText::_('YOU_ARE_NOT_GRANTED_ACCESS_TO_THIS_QUIZ') ;
				$type = 'notice' ;
			}	
				
			$link =  JRoute::_('index.php?option='. $option . '&view=quizzes') ;
			$this->setRedirect($link, $msg, $type) ;
		}
	}
	
	function submitAnswer() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$model =& $this->getModel('quiz') ;
                $bool = JRequest::getBool('showResults');
		$show = ($bool)? 1 : 0 ;

		if ($model->storeAnswers()) 
		{
			$timeUp = JRequest::getVar('timeUp') ;
			if ((int)$timeUp == 1) {
				$notice = JText::_('TIME_UP_YOU_ANSWERS_HAVE_BEEN_SAVED') ;
				JError::raiseNotice('', $notice);
			} 
			$msg = JText::_( 'THANK_YOU_FOR_TAKING_THIS_QUIZ' ) ;

            
			$this->_user =& JFactory::getUser();
            
            if ($this->_user->guest == 1) {
                $link = JRoute::_('index.php?option=com_jquarks&vr='.$show.'&view=who&id=' . $model->getSessionId(), false ) ;
            }
            else
            {
                if($bool) {
                    $link = JRoute::_('index.php?option=com_jquarks&view=session&id=' . $model->getSessionId(). "&". JUtility::getToken() . "=1", false) ;
                } else {
                    $link = JRoute::_('index.php?option=com_jquarks&view=quizzes', false) ;
                }
            }
            
		} 
		else 
		{
			$msg = JText::_( 'ERROR_YOUR_ANSWERS_COULD_NOT_BE_SAVED' ) ;
			$link = JRoute::_('index.php?option=com_jquarks&view=quizzes', false) ;
		} 
		
		$this->setRedirect($link, $msg) ;
	}	
	
}