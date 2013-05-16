<?php
/**
 * JQuarks Component Setofquestions Controller
 * 
 * @version		$Id: setofquestions.php 59 2010-03-02 08:40:27Z fnaccache $
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


class JQuarksControllerSetofquestions extends JQuarksController 
{
	public $_model ;
	
	function __construct() 
	{
		parent::__construct();
		
		$this->_model = $this->getModel('setofquestions') ;
		$this->registerTask( 'apply', 'save' );
	}	
	
	function display() 
	{
		JRequest::setVar( 'hidemainmenu', 1 );
		JRequest::setVar('view','setofquestions') ;
		
		parent::display() ;
	}
	
	function save() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$quizModel	     =& JModel::getInstance('quiz','JQuarksModel');
		$quizzesModel	 =& JModel::getInstance('quizzes','JQuarksModel');
		
		$array = JRequest::getVar('cid', 0, '', 'array') ;
	 	$setId = (int)$array[0] ;
		
		$error = null ;
		if (JRequest::getString('type') == 'random') {
			$error = $this->_model->checkCoherenceRandomSet() ;
		}
		
		if ($error['type'] == 1) // case of lack of question
		{
			array_shift($error) ; // removing the type of error
			
			$msg = JText::_('ERROR_THIS_SET_REQUIRE_A_NUMBER_OF_QUESTIONS_FOR_SELECTED_CATEGORY_CONFLICTING_WITH_REMAINING_QUESTIONS_FOR_QUIZZES') . ' : <br /><br />' ;

			foreach ($error AS $quiz) {
				$msg .= JText::sprintf('ONLY_ALLOW_QUESTIONS_TO_BE_ADDED_TO_THIS_CATEGORY', $quiz['title'], $quiz['remaining'])  . '<br />' ; 
			}
			
			$type = "error" ;
		}
		elseif ($this->_model->store()) 
		{
			$notice = "" ;
			
			// checking the case where editing this set has caused quizzes to become empty
			if ((int)JRequest::getInt('needednumber') == 0) 
			{
				$quizzesThatHaveThisSet = $quizzesModel->getQuizzesThatHaveSet($setId) ;
 
				foreach ($quizzesThatHaveThisSet AS $quiz)
				{
					$questionsAffectedToQuiz = $quizModel->getQuestionsAffectedToQuiz($quiz['id']) ; 
					if (!count($questionsAffectedToQuiz) && !$quizModel->getNbrRandomQuestionsAffectedToQuiz($quiz['id']) ) 
					{
						if (!$quizzesModel->unPublish($quiz['id']))
						{
							$msg = JText::_( 'ERROR_UNPUBLISHING_QUIZ' ) ;
							$type = 'error' ;
						} else {
							$notice = JText::_('EMPTY_QUIZZES_HAVE_BEEN_UNPUBLISHED') . '<br /><br />' ;
						}
					}
				}
			}
			
			if ($error['type'] == 2 ) // case of unassignation of set
			{
				array_shift($error) ; // removing the type of error
				
				$notice .= JText::_('THIS_EMPTY_SET_HAS_BEEN_UNAFFECTED_FROM_THESE_QUIZZES') . ' : <br /><br />' ;
				
				foreach ($error AS $quiz) {
					$notice .= '"' . $quiz['title'] . '"<br />' ;  
				}
				
				$notice .= '<br />' ;

				if (!$quizModel->unassignSetFromQuizzes($setId) ) // unassigning the set from all quizzes 
				{
					$msg = JText::_( 'ERROR_UNAFFECTING_SET_FROM_QUIZZES' ) ;
					$type = 'error' ;
				}
				else
				{
					$msg = JText::_( 'SET_OF_QUESTIONS_SAVED' ) ;
					$type = "message" ;
				}
				
				JError::raiseNotice('', $notice);
			}
			else
			{
				$msg = JText::_( 'SET_OF_QUESTIONS_SAVED' ) ;
				$type = "message" ;
			}
		} 
		else 
		{
			$msg = JText::_( 'ERROR_SAVING_SET_OF_QUESTIONS' ) ;
			$type = "error" ;			
		} 
		
		if ($this->getTask() == 'save') {
			$link = 'index.php?option=com_jquarks&controller=setsofquestions' ;
		} else {
			$link = 'index.php?option=com_jquarks&controller=setofquestions&task=edit&cid[]=' . $this->_model->getId() ;
		}
		
		$this->setRedirect($link, $msg, $type) ;
	}
	
	function countQuestions()
	{
		$mainframe =& JFactory::getApplication();
		$model = $this->getModel('setofquestions') ;
		$data = JRequest::get('GET') ;
		
		$response['nbrQuestions'] = $model->getCategoryQuestionCountAjax($data['categoryId']) ;
		
		/*$document =& JFactory::getDocument();
		$document->setMimeEncoding( 'application/json' );
		JResponse::setHeader( 'Content-Disposition', 'attachment; filename="countQuestions.json"' );
		*/
		header('Content-type: text/plain');
		echo json_encode($response) ;
		
		$mainframe->close() ;
	}
	
	/**
	 * Assign Question to the current set of questions
	 * 
	 * @return void
	 */
	function assignQuestion() 
	{
		$mainframe =& JFactory::getApplication();
		$model           = $this->getModel('setofquestions');
		$quizModel 		 =& JModel::getInstance('quiz','JQuarksModel');
		$quizzesModel 	 =& JModel::getInstance('quizzes','JQuarksModel');
		
		$data   = JRequest::get('GET') ;
		$errors = $model->checkCoherenceCustomSet($data) ;
		
		
		$setEmpty 			= array_slice($errors, 0, 1) ;
		$quizzesEmpty 		= array_slice($errors, 1, 1) ;
		$questionDuplicate 	= array_slice($errors, 2, 1) ;
		$randomLack 		= array_slice($errors, 3, 1) ;
		
		$haveError = false ;                      // no error have occured
		$unassigningEmptySetFromQuizzes = true ;  // flag for error while unassigning the set from the quizzes 
		$unpublishingEmptyQuizzes = true ;        // flag for error while unpublishing all empty quizzes
		
		$return = '{"notices": [' ;
		
		if (count($setEmpty['setEmpty'])) // unassigning the last question from the set
		{
			if ($quizModel->unassignSetFromQuizzes($setEmpty['setEmpty']['setId'])) { // unassigning the set from the quizzes 
				$return .= ' { "message": "' . JText::_('THIS_SET_IS_NOW_EMPTY_AND_THUS_HAS_BEEN_UNAFFECTED_FROM_ALL_QUIZZES') . '", },' ;
			}
			else
			{
				$haveError = true ;
				$unassigningEmptySetFromQuizzes = false ;
			}
		}
		
		if (count($quizzesEmpty['quizzesEmpty'])) // unassigning the question caused quizzes to became empty
		{
			$unpublishSuccess = true ;
			
			foreach ($quizzesEmpty['quizzesEmpty'] AS $quiz) // thoses quizzes are to be unpublished
			{
				if (!$quizzesModel->unPublish($quiz['quizId'])) {
					$unpublishSuccess = false ;
				}
			}
			
			if ($unpublishSuccess) { // unpublishing the quizzes
				$return .= '{ "message": "' . JText::_('UNAFFECTING_THIS_SET_RESULTED_IN_MAKING_SOME_QUIZZES_EMPTY_THOSE_QUIZZES_HAVE_BEEN_UNPUBLISHED') . '", },' ;	
			}
			else
			{
				$haveError = true ;
				$unpublishingEmptyQuizzes = false ;
			}
		}
		
		if (count($questionDuplicate['questionDuplicate'])) // duplicated question in one or severals quizzes
		{
			$return .= '{ "quizzes": [' ;
			foreach ($errors['questionDuplicate'] AS $duplicate) {
				$return .= '{ "title": "' . $duplicate['quizTitle'] . '", },';
			}				
			$return .= ' ],' ;
			
			$duplicate['questionTitle'] = str_replace("\"", '\'', $duplicate['questionTitle'] ) ;
			$duplicate['questionTitle'] = str_replace("\n", "", $duplicate['questionTitle'] ) ;
			$duplicate['questionTitle'] = str_replace("\r", "", $duplicate['questionTitle'] ) ;
			
			$return .= ' "message": "' . JText::sprintf('THE_QUESTION_IS_DUPLICATED_IN_QUIZZES', $duplicate['questionTitle']) .'" },' ;
		}
		$return .= '],' ;
		
		$return .= ' "errors": [' ;
		if (count($randomLack['randomLack'])) // random sets of question affected to quizzes have no more enough number of questions
		{
			$haveError = true ;
			
			$return .= '{ "lack": [' ;
			foreach ($randomLack['randomLack'] AS $quizLackQuestions) 
			{
				$return .= '{ "quizTitle": "' . $quizLackQuestions['quizTitle'] . '",' .
						   ' "setTitle": "' . $quizLackQuestions['setTitle'] . '" },';
			}
			$return .= ' ],' ;
			
		   $return .= ' "message": "' . JText::_('ALTERING_QUESTION_ASSIGNATION_WILL_MAKE_QUIZZES_HAVE_NOT_ENOUGH_QUESTIONS_AVAILABLE_FOR_THEIR_ASSIGNED_RANDOM_SETS') . '",' .
		   '},' ;
		}
		
		if ($haveError || !$model->assignQuestion()) {
			$return .= '{ "message": "' . JText::_('ERROR_UNABLE_TO_CHANGE_ASSIGNATION_STATE') . '", },' ;
		}
		
		if (!$unassigningEmptySetFromQuizzes) { // an error occured when unassigning empty set from quizzes
			$return .= ' { "message": "' . JText::_('ERROR_UNAFFECTING_THIS_EMPTY_SET_FROM_ALL_QUIZZES') . '", },' ;
		}
			
		if (!$unpublishingEmptyQuizzes) { // an error occured when unpublishing empty quizzes
			$return .= '{ "message": "' . JText::_('ERROR_UNPUBLISHING_EMPTY_QUIZZES') . '", },' ;
		}
		
		$return .= '], }' ;
		
		header('Content-type: text/plain');
		echo $return ; 
		
		$mainframe->close();
		
	}
		
	/**
	 * According to the filter selected by the user in category regenerate the list of questions
	 * 
	 * @return array JSON
	 */
	function filter_questions() 
	{
		$mainframe =& JFactory::getApplication();
	 	$model = $this->getModel('setofquestions');
		$questions = $model->getQuestions() ;
		
		$return = '{"questions": [' ;

		foreach ($questions as $key => $question) 
		{
			if (!$question->assigned_id) { 
				$question->assigned_id = 0 ; 		
			}

			if (!$question->category) { // uncategorised question
				$question->category = JText::_('UNCATEGORIZED') ;
			}
			
			$question->statement = str_replace("\"", '\'', $question->statement ) ;
			$question->statement = str_replace("\n", "", $question->statement ) ;
			$question->statement = str_replace("\r", "", $question->statement ) ;
		
		    $return  .= '{ "id" : ' . $question->id . ', ' .
                     '"assigned_id" : ' . $question->assigned_id  . ', ' .
                     '"category" : "' . $question->category . '", ' .
                     '"value" : "' . $question->statement . '" ' ;
                     
            if ( $key == count($questions) - 1 ) {
                $return .= '} ' ;   
            } else {
                $return .= '}, ' ;  
            }	
		}
		$return .= ']}' ;
		
		header('Content-type: text/plain');
		echo $return ;
		
		$mainframe->close();
	}
	
	/**
	 *  unassign Question from the current set of questions
	 * @return void
	 */
	/*function unassignQuestion() {
		
		global $mainframe ;
		$return = "<?xml version=\"1.0\" encoding=\"utf8\" ?>";
		$return .= "<options>";
		
		$model = $this->getModel('setofquestions');
		if ($model->assign()) {
			$return .= "<msg>" . JJText::_( 'QUESTION_UNASSIGNED_FROM_THIS_SET' ) . "</msg>" ;
		} else {
			$return .= "<msg>" . JText::_( 'ERROR_UNABLE_TO_UNASSIGN_THIS_QUESTION') . "</msg>" ;
		}
		$return .= "</options>";
		echo $return ;
		$mainframe->close();
		/*JRequest::checkToken() or jexit( 'Invalid Token' );
		
		if ($this->_model->assign()) {
			$msg = JText::_( 'QUESTION_UNASSIGNED_FROM_THIS_SET' ) ;
		} else {
			$msg = JText::_( 'ERROR_UNABLE_TO_UNASSIGN_THIS_QUESTION') ;
		}

		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;
		
		$link = 'index.php?option=com_jquarks&controller=setofquestions&task=edit&cid[]=' . $cids[0] ;
		$this->setRedirect($link, $msg) ;*/
	//}
	
	/**
	 *  change the type of set from custom to random and vice-versa
	 * @return void
	 */
	function changeType() 
	{
		JRequest::checkToken() or jexit('Invalid Token') ;
		
		if ($this->_model->changeType()) 
		{
			$msg = JText::_( 'TYPE_SET_CHANGE_SUCCESS' ) ;
			$type = "message" ;
		} 
		else 
		{
			$msg = JText::_( 'TYPE_SET_CHANGE_FAILURE' ) ;
			$type = "error" ;
		}
		
		$id = $this->_model->getId() ;
		$link = 'index.php?option=com_jquarks&controller=setofquestions&task=edit&cid[]=' . $id ;
		
		$this->setRedirect($link, $msg, $type) ;
	}
	
	function cancel() 
	{
		$this->setRedirect( 'index.php?option=com_jquarks&controller=setsofquestions' ) ;
	}
	
}