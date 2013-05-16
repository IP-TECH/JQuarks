<?php
/**
 * JQuarks Component Setofquestions Model
 * 
 * @version		$Id: setofquestions.php 59 2010-03-02 08:40:27Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Models
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


class JQuarksModelSetofquestions extends JModel 
{
	
	public $_id ; 
	public $_setofquestions ;
	public $_questions ;
	
	public $_categoryQuestionCount ;
	
	public $_assignedQuestions ;
	public $_pageNav ;
	
	public $_filter_category_custom ;
	public $_filter_category_random ;
	public $_filter_assigned ;
	public $_filter_order ;
	public $_filter_order_Dir ;
	
	public $_setsofquestions_questionsTable ;
	public $_setofquestionsTable ;
	
	function __construct($id = null) 
	{
		parent::__construct();

		if ($id) {
			$this->_id = $id ;
		} 
		else 
		{
			$array = JRequest::getVar('cid', 0, '', 'array') ;
	 		$this->_id = (int)$array[0] ;	
		}
	}

	/**
	 * Get the selected set of questions
	 * 
	 * @param  $setofquestionsId
	 * @return array
	 */
	function &getSetOfQuestions($setofquestionsId = null) 
	{
		if ($setofquestionsId) {
			$this->_id = $setofquestionsId ; 
		}
		
		$query = 'SELECT setsOfQuestions.*, randomSets.category_id, randomSets.nquestions, categories.title AS category,' .
		' (SELECT count(*) 
		   FROM #__jquarks_setsofquestions_questions AS soqHaveQ 
		   where soqHaveQ.setofquestions_id = ' . $this->_id . ' ) AS customCount' .
		' FROM #__jquarks_setsofquestions AS setsOfQuestions' .
		' LEFT JOIN #__jquarks_customsets AS customSets ON customSets.set_id = setsOfQuestions.id' .
		' LEFT JOIN #__jquarks_randomsets AS randomSets ON randomSets.set_id = setsOfQuestions.id' .
		' LEFT JOIN #__jquarks_categories AS categories ON categories.id = randomSets.category_id' .
		' WHERE setsOfQuestions.id = ' . $this->_id ;
		
		$this->_db->setQuery($query) ;

		$this->_setofquestions = $this->_db->loadObject() ;
		if ($this->_db->getErrorNum()) {
			return false ;
		}
		
		if (!$this->_setofquestions) 
		{
			$this->_setofquestions = new stdClass() ;
			$this->_setofquestions->id = 0 ;
			$this->_setofquestions->title = '' ;
			$this->_setofquestions->category_id = 0 ;
			$this->_setofquestions->nquestions = 0 ;
			$this->_setofquestions->category = '';
			$this->_setofquestions->type = '' ;
		}	
		
		return $this->_setofquestions ;
	}
	
	function getId() 
	{
		return $this->_id ;
	}
	
	/**
	 * Get the list of questions according to the user's filters
	 * 
	 * @return array
	 */
	function &getQuestions() 
	{		
		$mainframe =& JFactory::getApplication();
		$context						= 'com_jquarks.setofquestions.custom.list.' ;
		$this->_filter_order 			= $mainframe->getUserStateFromRequest( $context.'filter_order', 'filter_order', 'statement', 'cmd' ) ;
		$this->_filter_order_Dir 		= $mainframe->getUserStateFromRequest( $context.'filter_order_Dir', 'filter_order_Dir', '', 'word') ;
		$this->filter_assigned			= $mainframe->getUserStateFromRequest( $context.'filter_assigned', 'filter_assigned', '', 'string') ;
		$this->filter_category_custom  	= $mainframe->getUserStateFromRequest( $context.'filter_category_custom', 'filter_category_custom', -1, 'string') ;
		$search							= $mainframe->getUserStateFromRequest( $context.'search', 'search', '', 'string') ;
		$search 						= JString::strtolower($search) ;
		
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int') ;
		
		$where = array();
		
		$orderby = '' ;
		
		if ($this->_filter_order_Dir){
			$orderby 	= ' ORDER BY '.$this->_filter_order.' '. $this->_filter_order_Dir ;
		}
		
		$where[] = '' ;

		if ($this->filter_category_custom == 0) { // case of uncategorized question
			$where[] = ' question.category_id IS NULL' ;
		}
		
		if ($this->filter_category_custom > 0) {
			$where[] = ' question.category_id = ' . (int) $this->filter_category_custom;
		}
		
		if ($this->filter_assigned) 
		{			
			if ($this->filter_assigned == 'Y') 
			{
				$where[] = ' question.id IN (SELECT distinct(soqh.question_id)' .
									    ' FROM #__jquarks_setsofquestions_questions as soqh' .
										' WHERE soqh.setofquestions_id = ' . $this->_id .
										' )' ;
			} 
			elseif ($this->filter_assigned == 'N') 
			{
				$where[] = ' question.id NOT IN (SELECT distinct(soqh.question_id)' . 
										' FROM #__jquarks_setsofquestions_questions as soqh' .
										' WHERE soqh.setofquestions_id = ' . $this->_id . 
										' )' ;
			}
		}	
		
		if ($search) {
			$where[] = 'LOWER(question.statement) LIKE '.$this->_db->Quote( '%'.$this->_db->getEscaped( $search, true ).'%', false );
		}
		
		$where	= count( $where ) ? ' ' . implode( ' AND ', $where ) : '' ;
		
		// getting the questions
		$query = 'SELECT question.*, category.title AS category,  (SELECT id' .
												  ' FROM #__jquarks_setsofquestions_questions AS setsofquestions_questions' .
												  ' WHERE setsofquestions_questions.question_id = question.id' .
												  ' AND setsofquestions_questions.setofquestions_id = ' . $this->_id . 
												  ') AS assigned_id' .
		' FROM #__jquarks_questions AS question' .
		' LEFT JOIN #__jquarks_categories AS category ON category.id = question.category_id' .	
		' WHERE question.archived = 0' .	
		$where .
		' GROUP BY question.id' .
		$orderby ;
		
		$this->_db->setQuery($query) ;

		// creating the pagination according to the number of set of questions
		$total = $this->_db->loadResult() ;
		
		jimport('joomla.html.pagination') ;
		$this->_pageNav = new JPagination( $total, $limitstart, $limit ) ;

		$this->_questions = $this->_getList( $query, $this->_pageNav->limitstart, $this->_pageNav->limit );
		if ($this->_db->getErrorNum()) {
			return false;
		}
		
		return $this->_questions ;	
	}
	
	/**
	 * get the list of questions that are affected to the set
	 * 
	 * @param $setId
	 * @return array
	 */
	function getQuestionsAffectedToSet($setId) 
	{
		$query = 'SELECT *' .
		' FROM #__jquarks_setsofquestions_questions AS soqHaveQ' .
		' WHERE soqHaveQ.setofquestions_id = ' . $setId ;
		
		return $this->_getList($query) ;
	}
	
	/**
	 * Get the number of question of this category
	 * 
	 * @return int the number of question in this category
	 */
	function getCategoryQuestionCount() 
	{
		$mainframe =& JFactory::getApplication();
		$context						= 'com_jquarks.setofquestions.random.list.' ;
		$this->filter_category_random  	= $mainframe->getUserStateFromRequest( $context.'filter_category_random', 'filter_category_random', 0, 'string') ;
		
		$categoryModel =& JModel::getInstance('category','JQuarksModel');
  
		if ( !$this->_setofquestions->category_id ) { // case of uncategorized random set
			return count($categoryModel->getQuestionsOfCategory('actived')) ; 
		} else {
			return count($categoryModel->getQuestionsOfCategory('actived', $this->_setofquestions->category_id)) ;
		}
	}
	
	function getCategoryQuestionCountAjax($categoryId)
	{
		$categoryModel =& JModel::getInstance('category','JQuarksModel');
		
		if ($categoryId == 0) { // case of uncategorized random set
			return count($categoryModel->getQuestionsOfCategory('actived')) ;
		} else {
			return count($categoryModel->getQuestionsOfCategory('actived', $categoryId)) ;	
		}
	}
	
	function &getLists() 
	{
		$mainframe =& JFactory::getApplication();
		$context						= 'com_jquarks.setofquestions.random.list.' ;
		$this->filter_category_random  	= $mainframe->getUserStateFromRequest( $context.'filter_category_random', 'filter_category_random', 0, 'string') ;
		
		// categories filter get the list of categories from the table categories and create a select box with them to be used as filter
		$query = 'SELECT c.id, c.title' .
		' FROM #__jquarks_categories AS c' ;
		
		$categories[] = JHTML::_('select.option', '-1', JText::_('ALL_CATEGORIES'));
		$categories[] = JHTML::_('select.option', '0', JText::_('UNCATEGORIZED'));
		$this->_db->setQuery($query) ;
				
		foreach( $this->_db->loadObjectList() as $obj ) {
			$categories[] = JHTML::_('select.option', $obj->id, $obj->title ); 
		}
		
		$lists['categoriesCustom'] = JHTML::_('select.genericlist',	$categories, 'filter_category_custom', 'class="inputbox" size="1" onchange="filter_questions();"', 'value', 'text',  $this->filter_category_custom);
		
		array_shift($categories) ;
		$lists['categoriesRandom'] = JHTML::_('select.genericlist',	$categories, 'filter_category_random', 'class="inputbox" size="1" ', 'value', 'text',  $this->_setofquestions->category_id);
		
		// list of status
		$status[] = JHTML::_('select.option', '', '- '.JText::_('SELECT_STATUS').' -');
		$status[] = JHTML::_('select.option', 'Y', JText::_('ASSIGNED')) ;
		$status[] = JHTML::_('select.option', 'N', JText::_('NOT_ASSIGNED')) ;
		
		$lists['status'] = JHTML::_('select.genericlist', $status, 'filter_assigned', 'class="inputbox" size="1" onchange="filter_questions();"', 'value', 'text', $this->filter_assigned) ;
		
		$lists['order_Dir']	= $this->_filter_order_Dir;
		$lists['order']		= $this->_filter_order;	
		
		return $lists ;
	}
			
	function &getPageNav() 
	{
		return $this->_pageNav ;
	}
	
	/**
	 *  return true if the set of question is random
	 * @param $id int
	 * @return int (0 => custom set, 1 => random set)
	 */
	/*function getType($id = null) {
		
		if ($id) {
			$this->_id = $id ;
		}
		
		$query = 'SELECT type' .
		' FROM #__jquarks_setsofquestions' .
		' WHERE id = ' . $this->_id ;
		
		$this->_db->setQuery() ;
		return $this->_db->loadResult() ;
	}*/
	
	/**
	 * Get the random set of the given category that is affected to this given quiz.
	 * 
	 * @param $quizId
	 * @param $categoryId
	 * @return array associative
	 */
	function getRandomSetOfCategoryAffectedToQuiz($quizId, $categoryId = NULL)
	{
		$query = 'SELECT randomSets.*, setsOfQuestions.title, setsOfQuestions.type' .
		' FROM #__jquarks_quizzes_setsofquestions AS quiHaveSoq' .
		' JOIN #__jquarks_setsofquestions AS setsOfQuestions ON setsOfQuestions.id = quiHaveSoq.setofquestions_id' .
		' JOIN #__jquarks_randomsets AS randomSets ON randomSets.set_id = setsOfQuestions.id' .
		' WHERE quiHaveSoq.quiz_id = ' . $quizId ;
		
		if ($categoryId) {
			$query .= ' AND randomSets.category_id = ' .$categoryId ;	
		} else { // case of uncategorized random set
			$query .= ' AND randomSets.category_id IS NULL' ;
		} 
		
		$this->_db->setQuery($query) ;
		return $this->_db->loadAssoc() ;
	}
	
	/**
	 * Get the list of all custom sets, random sets and quizzes that may be affected by the change of affectation status of this question to the set
	 * 
	 * @param $data array {questionId, soqId}
	 * @return mixed list of quizzes and sets that may become affected
	 */
	function checkCoherenceCustomSet($data) 
	{
		$questionModel   =& JModel::getInstance('question','JQuarksModel');
		$categoryModel	 =& JModel::getInstance('category','JQuarksModel');
		$quizModel 		 =& JModel::getInstance('quiz','JQuarksModel');
		$quizzesModel 	 =& JModel::getInstance('quizzes','JQuarksModel');
		
		$category = $questionModel->getCategoryOfQuestion($data['questionId']) ; 


		if($category) {
			$totalNbrQuestions = count($categoryModel->getQuestionsOfCategory('actived', $category['id'])) ;
		} else { // case of uncategorized questions
			$totalNbrQuestions = count($categoryModel->getQuestionsOfCategory('actived')) ;
		}
		
		$quizzesThatHaveThisSet = $quizzesModel->getQuizzesThatHaveSet($data['soqId']) ;

		$errors = array(
			'setEmpty' => null,
			'quizzesEmpty' => null,
			'questionDuplicate' => null,
			'randomLack' => null,
		) ;
		
		if($quizzesThatHaveThisSet) 
		{
			foreach($quizzesThatHaveThisSet AS $quiz)
			{
				$nbrRandomQuestionAffectedToQuiz = $quizModel->getNbrRandomQuestionsAffectedToQuiz($quiz['id']) ;
				
				// case user try to unassign the question from the set
				if ($questionModel->questionAssignedToSet($data['questionId'], $data['soqId']))  
				{
					$customQuestionsAffectedToQuiz = $quizModel->getQuestionsAffectedToQuiz($quiz['id']) ; // getting the number of questions affected to the quiz
					
					if (count($this->getQuestionsAffectedToSet($data['soqId'])) == 1) // checking if this is the last question of the set
					{
						$errors['setEmpty']['setId'] = $data['soqId'] ;
					}
					
					if ( (count($customQuestionsAffectedToQuiz) == 1) && ($nbrRandomQuestionAffectedToQuiz == 0) ) // checking if this is the last question of the quiz 
					{
						$errors['quizzesEmpty'][$quiz['id']]['quizId'] = $quiz['id'] ;
						$errors['quizzesEmpty'][$quiz['id']]['quizTitle'] = $quiz['title'] ;
					}
				} 
				else // case user try to add a question to the set 
				{ 
					// getting the question of this category that are affected to the quiz
					// getting the random set of this category that is affected to this quiz
					if($category['id']) { 
						$questionsOfCategoryAffectedToQuiz = $quizModel->getQuestionsOfCategoryAffectedToQuiz($quiz['id'], $category['id']) ;
						$randomSet = $this->getRandomSetOfCategoryAffectedToQuiz($quiz['id'], $category['id']) ;
					} else {
						$questionsOfCategoryAffectedToQuiz = $quizModel->getQuestionsOfCategoryAffectedToQuiz($quiz['id']) ;
						$randomSet = $this->getRandomSetOfCategoryAffectedToQuiz($quiz['id']) ;
					}

					// checking if the question is not already affected to quiz
					foreach ($questionsOfCategoryAffectedToQuiz AS $question) 
					{
						if ($question['id'] == $data['questionId']) 
						{
							$errors['questionDuplicate'][$quiz['id']]['quizTitle'] = $quiz['title'] ;
							$errors['questionDuplicate'][$quiz['id']]['questionTitle'] = $question['statement'] ;
						}
					}
					
					// checking the number of questions for random set is respected
					if ($randomSet && ($randomSet['nquestions'] + count($questionsOfCategoryAffectedToQuiz) + 1 > $totalNbrQuestions) ) 
					{
						$errors['randomLack'][$quiz['id']]['quizTitle'] = $quiz['title'] ;
						$errors['randomLack'][$quiz['id']]['setTitle'] = $randomSet['title'] ;   
					}
				}
			}
		}
		
		return $errors ;
	}
	
	/**
	 * Check the coherence of a random set of question with the quizzes he is affected to :
	 * we check for each quiz this set is affected to whether or not the number of questions needed is permited by the set
	 * we also check if the set's needed number of questions is put to zero in order to unassign it from quizzes
	 *  
	 * @return array containing quizzes
	 */
	function checkCoherenceRandomSet()
	{
		$mainframe =& JFactory::getApplication();
		$categoryModel   =& JModel::getInstance('category','JQuarksModel');
		$quizModel    	 =& JModel::getInstance('quiz','JQuarksModel');
		$quizzesModel    =& JModel::getInstance('quizzes','JQuarksModel');
		
		$context						= 'com_jquarks.setofquestions.random.list.' ;
		$this->filter_category_random  	= $mainframe->getUserStateFromRequest( $context.'filter_category_random', 'filter_category_random', 1, 'string') ;
		
		$array = JRequest::getVar('cid', 0, '', 'array') ;
	 	$setId = (int)$array[0] ;
		
		$available = JRequest::getInt('availablenumber') ;
		$needed = (int)JRequest::getInt('needednumber') ;
		
		$quizzesThatHaveThisSet = $quizzesModel->getQuizzesThatHaveSet($setId) ;
		
		$error = null ;
		
		foreach ($quizzesThatHaveThisSet AS $quiz) 
		{
			$questionOfCategoryAffected = count($quizModel->getQuestionsOfCategoryAffectedToQuiz($quiz['id'], $this->filter_category_random)) ;
			
			if ($questionOfCategoryAffected + $needed > $available) // if the needed number of question needed is not available
			{
				$error['type'] = 1 ;
				$error[$quiz['id']]['id'] = $quiz['id'] ;
				$error[$quiz['id']]['title'] = $quiz['title'] ;
				$error[$quiz['id']]['remaining'] = $available - $questionOfCategoryAffected ;
			}
			
			if ($needed == 0) // if the number of questions needed is set to zero
			{
				$error['type'] = 2 ;
				$error[$quiz['id']]['id'] = $quiz['id'] ;
				$error[$quiz['id']]['title'] = $quiz['title'] ;
			}
		}

		return $error ;
	}
	
	/**
	 * Assign/unassigne a questions to/from a set
	 * 
	 * @return boolean
	 */
	function assignQuestion() 
	{
		$questionModel   =& JModel::getInstance('question','JQuarksModel');
		$this->_rowsetsofquestions_questions =& JTable::getInstance('setsofquestions_questions', 'Table') ;
		$this->_setsofquestions_questionsTable =& JTable::getInstance('setsofquestions_questions', 'Table') ;
		
		$data = JRequest::get('GET') ;
		$line['question_id'] = $data["questionId"] ;
		$line['setofquestions_id'] = $data["soqId"] ;
		
		// getting the assignation id of the question to the set 
		$assigned = $questionModel->questionAssignedToSet($line['question_id'], $line['setofquestions_id']) ;
		
		if ($assigned) 
		{
			if (!$this->_setsofquestions_questionsTable->delete( $assigned ))
			{
				$this->setError($this->_db->getErrorMsg() ) ;
				return false ;
			}
		} 
		else 
		{
			$line['id'] = 0 ; 
			
			if (!$this->_setsofquestions_questionsTable->save($line)) {
				return false ;
			}
		}	
		
		return true ;
	}
	
	/**
	 * Get the set of question's ID of a custom set
	 * 
	 * @param $set
	 * @return id of the set
	 */
	function getCustomId($set) 
	{
		$query = 'SELECT id' .
		' FROM #__jquarks_customsets' .
		' WHERE set_id = ' . $set ;
		
		$this->_db->setQuery($query) ;
		$id = $this->_db->loadResult() ;
		
		if ($id) {
			return($id) ;
		} else {
			return 0 ;
		}
	}
	
	/**
	 * Get the set of question's ID of a random set
	 * 
	 * @param $set
	 * @return id of the set
	 */
	function getRandomId($set) 
	{
		$query = ' SELECT id' .
		' FROM #__jquarks_randomsets' .
		' WHERE set_id = ' . $set ;
		
		$this->_db->setQuery($query) ;
		$id = $this->_db->loadResult() ;
		
		if ($id) {
			return($id) ;
		} else {
			return 0 ;
		}
	}
	
	/**
	 * Switch the current set's type random -> custom and custom -> random 
	 * cusotm = 0 random = 1
	 * 
	 * @return boolean
	 */
	function changeType() 
	{ 
		$this->_randomsetTable =& JTable::getInstance('randomset', 'Table') ;
		$this->_customsetTable =& JTable::getInstance('customset', 'Table') ;
		
		$cids = JRequest::getVar('cid', 0, '', 'array') ;
		$data['id'] = (int)$cids[0] ;
		$data['type'] = JRequest::getString('type') ;
		
		$type = (int)JRequest::getInt('soqtype') ; // soqtype = 0 custom soqtype = 1 random
		
		if ($type) {
			$type = 'random' ;
		} else {
			$type = 'custom' ;
		}

		if (!$this->store()) {
			return false ;
		}
	
		unset($data['type']) ;

		if ( $data['id'] != 0 ) 
		{
			if ( $type == 'custom' ) // delete the random set 
			{
				$randomId = $this->getRandomId($data['id']) ;

				if(!$this->_randomsetTable->delete($randomId)) 
				{
					$this->setError( $this->_db->getErrorMsg() ) ;
					return false ;
				}
			}
			
			if ( $type == 'random' ) // delete the custom set 
			{
				$customId = $this->getCustomId($data['id']) ;
				
				if(!$this->_customsetTable->delete($customId)) 
				{
					$this->setError( $this->_db->getErrorMsg() ) ;
					return false ;
				}
			}
		}
	 	
		return true ;	
	}
	
	/**
	 * Store the set of question
	 * 
	 * @return boolean
	 */
	function store() 
	{
		$this->_row =& $this->getTable() ;
		$this->_randomsetTable =& JTable::getInstance('randomset', 'Table') ;
		$this->_customsetTable =& JTable::getInstance('customset', 'Table') ;
		
                $mainframe =& JFactory::getApplication();
		$context						= 'com_jquarks.setofquestions.random.list.' ;
		$this->filter_category_random  	= $mainframe->getUserStateFromRequest( $context.'filter_category_random', 'filter_category_random', 1, 'string') ;
		
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;
		
		$data['id'] = $cids[0] ;
		$data['title'] = JRequest::getVar('title') ;
		$data['type'] = JRequest::getString('type') ;
		
		$type = (int)JRequest::getInt('soqtype') ; 
		
		if ($type) {
			$type = 'random' ;
		} else {
			$type = 'custom' ;
		}
		
		$data['type'] = $type ;
		
		if(!$this->_row->save($data)) // saving the set of questions
			return false ;
		
		unset($data['type']) ; 
		unset($data['title']) ;
			
		if ($data['id'] == 0) { // case of a new set
			$this->_id = $data['set_id'] = $this->_db->insertid() ;
		} else { 
			$data['set_id'] = $data['id'] ;			
		}
	
		if($type == 'custom') // store the custom set
		{
			$data['id'] = $this->getCustomId($data['set_id']) ;
			
			if(!$this->_customsetTable->save($data)) { 
				return false ;			
			}
		}
		
		if($type == 'random') // store the random set 
		{
			$data['id'] = $this->getRandomId($data['set_id']) ;
			
			$data['category_id'] = $this->filter_category_random ;
			if ( $data['category_id'] == 0 || $data['category_id'] == -1 ) { // case of uncategorized random set or not selected random set
				$data['category_id'] = NULL ;
			} 
			
			$available = JRequest::getInt('availablenumber') ;
			$needed = JRequest::getInt('needednumber') ;
			
			if($available < $needed) { 
				$needed = $available ;
			}
			
			$data['nquestions'] = $needed ;
			
			if (!$this->_randomsetTable->bind($data))
			{
				echo $this->_db->getErrorMsg() ;
				return false ;
			}
			
			if (!$this->_randomsetTable->store(true)) // with first param set to true
			{
				echo $this->_db->getErrorMsg() ;
				return false ;
			}
		}
		
		return true ;
	}
	
}