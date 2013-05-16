<?php
/**
 * JQuarks Component Quiz Controller
 * 
 * @version		$Id: quiz.php 81 2010-11-30 13:09:45Z fnaccache $
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


class JQuarksControllerQuiz extends JQuarksController 
{
	public $_model ;
	
	function __construct() 
	{
		parent::__construct();
		
		$this->_model = $this->getModel('quiz') ;
		
		$this->registerTask( 'apply', 'save' );
		$this->registerTask( 'applyNotif', 'saveNotif' );
//		$this->registerTask( 'assignAll', 'assignUsers' );
	}
	
	function display($tpl = "") 
	{
		JRequest::setVar( 'hidemainmenu', 1 );
		JRequest::setVar('view','quiz') ;
		
		parent::display($tpl) ;
	}
		
	/**
	 * Assign one set of question to the current quiz
	 * 
	 * @return void
	 */
	function assignSet() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$set = $this->_model->randomExist() ;   
		$setStats = $this->_model->checkCoherentSets();
		$id = $this->_model->getId() ; 
		
		if ($set['id']) 
		{
			// case a random set for this category already exist
			$msg = JText::_( 'ERROR_UNABLE_TO_ASSIGN_SET_OF_QUESTIONS') ;
			$type = "error" ;
			$notice = JText::_( 'QUIZ_CONTAIN_RANDOM_FOR_CATEGORY' ) . ' : "' . $set['title'] . '"' ;

			JError::raiseNotice('', $notice);
		} 
		elseif ( !$setStats['coherent'] ) 
		{
			array_pop($setStats) ;
			
			$msg = JText::_( 'ERROR_NOT_ENOUGH_QUESTIONS_AVAILABLE' ) ;
			$type = "error" ; 
			$notice = "" ;
			foreach ($setStats as $setStat) 
			{
				if ($setStat['affectedToThisSet'] + $setStat['affectedToThisQuiz'] > $setStat['totalNumber']) 
				{
					$notice = JText::_('CATEGORY') . ' : "' . $setStat['categoryName'] . '" ' . JText::_('HAVE_A_TOTAL_OF') . ' ' . $setStat['totalNumber'] . ' ' . JText::_('QUESTIONS')
					. ', ' . JText::_('CURRENTLY_AFFECTED') . ' : ' . $setStat['affectedToThisQuiz'] . ', ' . JText::_('SET_CONTAIN') . ' : ' . $setStat['affectedToThisSet'] ;
				} 
			}

			JError::raiseNotice('', $notice) ;
		} 
		else 
		{
			if ($this->_model->assignSet()) 
			{
				$msg = JText::_( 'SET_OF_QUESTIONS_ASSIGNED_TO_THIS_QUIZ') ;
				$type = "message" ;
				$id = $this->_model->getId() ;
	
				$duplicate = $this->_model->getDuplicateQuestions($id) ;
				if(!empty($duplicate)) 
				{
					$notice = JText::_('CONTAIN_DUPLICATED_QUESTIONS') . '<br /><br />' ;
					foreach ($duplicate as $dupli) {
						$notice .= JText::_('Question') . ' : ' .$dupli['statement'] . JText::_('EXIST_IN_SET') . ' : "' . $dupli['title'] . '"<br />' ;
					}
					$notice .= '<br />' . JText::_( 'DUPLICATE_QUESTION_IGNORED' ) ;
					
					JError::raiseNotice('', $notice);
				}
			} 
			else 
			{
				$msg = JText::_( 'ERROR_SET_DONT_CONTAIN_QUESTIONS' ) ;
				$type = "error" ;
			}
		}
		
		$link = 'index.php?option=com_jquarks&controller=quiz&layout=assignSets&cid[]=' . $id ;
		$this->setRedirect($link, $msg, $type) ;
	}
	
	/**
	 * Unassign a set of question from the current quiz
	 * 
	 * @return void
	 */
	function unassignSet() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$quizzesModel    =& JModel::getInstance('quizzes','JQuarksModel');
		$quiz            =& $this->_model->getQuiz() ;
		$quiz->id        = (int)$quiz->id ;
		$quiz->published = (int)$quiz->published ;
	
		if ($this->_model->unassignSet()) 
		{
			$msg = JText::_( 'SET_OF_QUESTIONS_UNASSIGNED_FROM_THIS_QUIZ') ;	
			$type = "message" ;		
		} 
		else 
		{
			$msg = JText::_( 'ERROR_UNABLE_TO_UNASSIGN_THIS_SET_OF_QUESTIONS') ;
			$type = "error" ;
		}
		
		$customQuestionsAffectedToQuiz = $this->_model->getQuestionsAffectedToQuiz($quiz->id) ;

		if ( $quiz->published && (count($customQuestionsAffectedToQuiz) == 0 ) && !$this->_model->getNbrRandomQuestionsAffectedToQuiz($quiz->id) )
        {
	        if (!$quizzesModel->unPublish($quiz->id))
	        {
	        	$msg = JText::_( 'ERROR_UNPUBLISHING_QUIZ' ) ;
	            $type = 'error' ;
	        } else {
	           JError::raiseNotice('', JText::_('THIS_QUIZ_HAVE_NO_SETS_AFFECTED_AND_HAVE_BEEN_UNPUBLISHED')) ;
	        }
        }
		
		$link = 'index.php?option=com_jquarks&controller=quiz&layout=assignSets&cid[]=' . $quiz->id ;
		$this->setRedirect($link, $msg, $type) ;
	}
	
	/**
	 * Assign one user to the quiz
	 * 
	 * @return void
	 */
	function assignUser() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		if ($this->_model->assignUser()) 
		{
			$msg = JText::_( 'THE_USER_HAVE_BEEN_ASSIGNED_TO_THIS_QUIZ') ;
			$type = "message" ;			
		} 
		else 
		{
			$msg = JText::_( 'ERROR_UNABLE_TO_ASSIGN_USER_TO_THE_QUIZ') ;
			$type = "error" ;
		}
		
		$id = $this->_model->getId() ;
		
		$link = 'index.php?option=com_jquarks&controller=quiz&layout=assignUsers&cid[]=' . $id ;
		$this->setRedirect($link, $msg, $type) ;
	}


	
	/**
	 * Unassign one user from the quiz
	 * 
	 * @return void
	 */
	function unassignUser() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		if ($this->_model->unassignUser()) 
		{
			$msg = JText::_( 'THE_USER_HAVE_BEEN_UNASSIGNED_FROM_THIS_QUIZ') ;	
			$type = "message" ;		
		} 
		else 
		{
			$msg = JText::_( 'ERROR_UNABLE_TO_UNASSIGN_USER_FROM_THE_QUIZ') ;
			$type = "error" ;
		}
		
		$id = $this->_model->getId() ;
		
		$link = 'index.php?option=com_jquarks&controller=quiz&layout=assignUsers&cid[]=' . $id ;
		$this->setRedirect($link, $msg, $type) ;
	}

   
    
	/**
	 * Send a mail notification to the user 
	 * 
	 * @return void
	 */
	function notifyUser() 
	{
		if($this->_model->notifyUser()) 
		{
			$msg = JText::_( 'THE_USER_HAVE_BEEN_NOTIFIED') ;
			$type = "message" ;
		} 
		else 
		{
			$msg = JText::_( 'ERROR_SENDING_NOTIFICATION') ;
			$type = "error" ;
		}
		
		$id = $this->_model->getId() ;
		
		$link = 'index.php?option=com_jquarks&controller=quiz&layout=assignUsers&cid[]=' . $id ;
		$this->setRedirect($link, $msg, $type) ;
	}
	

	
	function save() 
	{
            JRequest::checkToken() or jexit( 'Invalid Token' );	
            $layout = JRequest::getVar('layout') ;
            
            if ($layout=="assignUsers") {
                		
		$layout = JRequest::getVar('layout') ;
		
		if ($this->_model->storeNotif()) 
		{
			$msg = JText::_( 'NOTIFICATION_MESSAGE_STORED' ) ;
			$type = "message" ;
		} 
		else 
		{
			$msg = JText::_( 'ERROR_WHILE_STORING_NOTIFICATION_MESSAGE' ) ;
			$type = "error" ;			
		}
		
		if ($this->getTask() == 'save') {
			$link = 'index.php?option=com_jquarks&controller=quizzes' ;
		} else {
			$link = 'index.php?option=com_jquarks&controller=quiz&task=edit&layout='. $layout .'&cid[]=' . $this->_model->getId() ;
		}
		
		$this->setRedirect($link, $msg, $type) ;
            }
            else{
                
		$bool = $this->_model->store();
                
                if ($bool)
                        {
                                $msg = JText::_( 'QUIZ_SAVED' ) ;
                                $type = "message" ;
                        } 
                        else 
                        {
                                $msg = JText::_( 'ERROR_SAVING_QUIZ' ) ;
                                $type = "error" ;			
                        } 

                        if ($this->getTask() == 'save') {
                                $link = 'index.php?option=com_jquarks&controller=quizzes' ;
                        } else {
                                $link = 'index.php?option=com_jquarks&controller=quiz&task=edit&cid[]=' . $this->_model->getId() ;
                        }
                        $this->setRedirect($link, $msg, $type) ;
            }
	}
	
//	function saveNotif() 
//	{
//		JRequest::checkToken() or jexit( 'Invalid Token' );
//		
//		$layout = JRequest::getVar('layout') ;
//		
//		if ($this->_model->storeNotif()) 
//		{
//			$msg = JText::_( 'NOTIFICATION_MESSAGE_STORED' ) ;
//			$type = "message" ;
//		} 
//		else 
//		{
//			$msg = JText::_( 'ERROR_WHILE_STORING_NOTIFICATION_MESSAGE' ) ;
//			$type = "error" ;			
//		}
//		
//		if ($this->getTask() == 'saveNotif') {
//			$link = 'index.php?option=com_jquarks&controller=quizzes' ;
//		} else {
//			$link = 'index.php?option=com_jquarks&controller=quiz&task=edit&layout='. $layout .'&cid[]=' . $this->_model->getId() ;
//		}
//		
//		$this->setRedirect($link, $msg, $type) ;
//	}
	
	function cancel() 
	{
		$this->setRedirect( 'index.php?option=com_jquarks&controller=quizzes' ) ;
	}


    /**
     * TODO
     * Assign all registered users to the quiz
     *
     * @return void
     */
    function assignUsers()
    {
        JRequest::checkToken() or jexit( 'Invalid Token' );

        if ($this->_model->assignUsers())
        {
            $msg = JText::_( 'THE_USER_HAVE_BEEN_ASSIGNED_TO_THIS_QUIZ') ;
            $type = "message" ;
        }
        else
        {
            $msg = JText::_( 'ERROR_UNABLE_TO_ASSIGN_USER_TO_THE_QUIZ') ;
            $type = "error" ;
        }

        $id = $this->_model->getId() ;

        $link = 'index.php?option=com_jquarks&controller=quiz&layout=assignUsers&cid[]=' . $id ;
        $this->setRedirect($link, $msg, $type) ;
    }


    /**
     * TODO
     * Unassign all registred users form the quiz
     *
     * @return void
     */
    function unassignUsers()
    {
        JRequest::checkToken() or jexit( 'Invalid Token' );

        if ($this->_model->unassignUsers())
        {
            $msg = JText::_( 'THE_USER_HAVE_BEEN_UNASSIGNED_FROM_THIS_QUIZ') ;
            $type = "message" ;
        }
        else
        {
            $msg = JText::_( 'ERROR_UNABLE_TO_UNASSIGN_USER_FROM_THE_QUIZ') ;
            $type = "error" ;
        }

        $id = $this->_model->getId() ;

        $link = 'index.php?option=com_jquarks&controller=quiz&layout=assignUsers&cid[]=' . $id ;
        $this->setRedirect($link, $msg, $type) ;
    }


    /**
     * TODO
     * Notify all the users affected to the current quiz
     *
     * @return void
     */
    function notifyUsers()
    {
        if ($this->_model->notifyUsers()) {
            $msg = JText::_('THE_USER_HAVE_BEEN_NOTIFIED');
            $type = "message";
        } else {
            $msg = JText::_('ERROR_SENDING_NOTIFICATION');
            $type = "error";
        }

        $id = $this->_model->getId();

        $link = 'index.php?option=com_jquarks&controller=quiz&layout=assignUsers&cid[]=' . $id;
        $this->setRedirect($link, $msg, $type);
    }
}
