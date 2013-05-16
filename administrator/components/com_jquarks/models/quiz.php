<?php
/**
 * JQuarks Component Quiz Model
 * 
 * @version		$Id: quiz.php 81 2010-11-30 13:09:45Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Models
 * @link		http://www.iptechinside.com/labs/projects/show/jquarks
 * @since		0.1
 * 
 */

defined('_JEXEC') or die();

jimport('joomla.application.component.model');


class JQuarksModelQuiz extends JModel 
{
	public $_id ;

	public $_quizTable ;
	public $_users_quizzesTable ;
	public $_quizzes_setsofquestionsTable ;
	
	public $_quiz ;
	public $_setsOfQuestions ;	
	public $_assignedSetsOfQuestions ;
	public $_assignedUsers ;
	public $_users ;
	
	public $_filter_order_Dir;
	public $_filter_order;
	
	public $_pageNavUsers ;
	public $_pageNavSets ;
	public $_lists ;
	
	function __construct() 
	{
		parent::__construct();
			   
		$array = JRequest::getVar('cid', 0, '', 'array') ;
	 	$this->_id = (int)$array[0] ;
	}
	
	/**
	 * Get the current quiz or create a new one
	 *  
	 * @param $quizId
	 * @return array
	 */
	function &getQuiz($quizId = null) 
	{		
		if ($quizId) {
			$this->_id = $quizId ;
		}
		
		if (empty($this->_quiz)) 
		{
			$query = 'SELECT quiz.*' .
			' FROM #__jquarks_quizzes AS quiz' .
			' WHERE quiz.id = ' . $this->_id ;
			
			$this->_db->setQuery($query) ;
			if ($this->_quiz = $this->_db->loadObject()) 
			{
				if ( $this->_quiz->publish_down == "00-00-00 00:00:00") {
					$this->_quiz->publish_down = JText::_('Never') ;
				}
	
				// creating the key 
				$paginate = array('use_pagination', 'use_slide', 'question_page') ;
				
				// making paginate an associative array containg the key created and the explode value it contained
				$this->_quiz->paginate = array_combine($paginate, explode(' ', $this->_quiz->paginate)) ;
	
				// slicing the attribute we only keep the value
				foreach ($this->_quiz->paginate as &$attrib) 
				{
					list($att, $value) = explode('=', $attrib) ;
					$attrib = $value ;
				}
				
				unset($attrib) ;
			}
			
		}
		
		if (!$this->_quiz) 
		{
			$config =& JFactory::getConfig();
			$jnow		=& JFactory::getDate();
			$jnow->setOffset( $config->getValue('config.offset' ));
			$now		= $jnow->toMySQL(true);
			
			$this->_quiz = new stdClass() ;
			$this->_quiz->id = 0;
			$this->_quiz->title = '' ;
			$this->_quiz->description = null ;
			$this->_quiz->published = false ;
			$this->_quiz->access_id = -1 ;
			$this->_quiz->time_limit = null ;
			$this->_quiz->unique_session = false ;
			$this->_quiz->paginate['use_pagination'] = 2 ; // 0 no pagination - 1 yes paginate - 2 use_global
			$this->_quiz->paginate['use_slide'] = 0 ;
			$this->_quiz->paginate['question_page'] = 0 ;
			$this->_quiz->publish_up = $now ;
			$this->_quiz->publish_down = JText::_('Never');
			$this->_quiz->notify_message = JText::_('GREETING') . ' [userName]' . ",\n\n" .
										JText::_('QUIZ_IS_AVAILABLE') . "\n\n" .
										JText::_('QUIZ') . ' : "[quizTitle]"' .  "\n" .
										JText::_('DESCRIPTION') . ' : "[quizDescription]"' .  "\n" .
										JText::_('WILL_BE_UNPUBLISHED') . ' : "[unpublishDate]"' . "\n\n" .
										JText::_('CONNECT_TO_ACCESS') . ' [quizLink]' . "\n\n" .
										JText::_('SEE_YOU_SOON') ;
			$this->_quiz->show_results = false;
		}	
		
		return $this->_quiz ;
	}
	
	function &getId() 
	{
		return $this->_id ;
	}
	
	/**
	 * Get the list of sets of questions
	 * 
	 * @return array
	 */
	function getSetsOfQuestions() 
	{
                $mainframe =& JFactory::getApplication();
		$context					= 'com_jquarks.quiz.sets.list.' ;
		$this->filter_assigned		= $mainframe->getUserStateFromRequest( $context.'filter_assigned_sets', 'filter_assigned_sets', '', 'string') ;
		$this->_filter_set_type		= $mainframe->getUserStateFromRequest( $context.'filter_setType', 'filter_setType', '', 'string') ;
		$search						= $mainframe->getUserStateFromRequest( $context.'search', 'search', '', 'string') ;
		$search 					= JString::strtolower($search) ;
		$this->_filter_order 		= $mainframe->getUserStateFromRequest( $context.'filter_order', 'filter_order', 'title', 'cmd' ) ;
		$this->_filter_order_Dir 	= $mainframe->getUserStateFromRequest( $context.'filter_order_Dir', 'filter_order_Dir', '', 'word') ;
		
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int') ;
		
		$where = array();
		
		$orderBy = '' ;
		
		if ($this->_filter_order_Dir){
			$orderBy 	= ' ORDER BY '.$this->_filter_order.' '. $this->_filter_order_Dir ;
		}
		
		if ($this->filter_assigned) 
		{			
			if ($this->filter_assigned == 'Y') 
			{
				$where[] = ' soq.id IN (SELECT distinct(quizzes_setsofquestions.setofquestions_id)' . 
											' FROM #__jquarks_quizzes_setsofquestions as quizzes_setsofquestions' .
											' WHERE quizzes_setsofquestions.quiz_id = ' . $this->_id . ' )' ;
			} 
			elseif ($this->filter_assigned == 'N') 
			{
				$where[] = ' soq.id NOT IN (SELECT distinct(quizzes_setsofquestions.setofquestions_id)' . 
											' FROM #__jquarks_quizzes_setsofquestions as quizzes_setsofquestions' .
											' WHERE quizzes_setsofquestions.quiz_id = ' . $this->_id . ' )' ;
			}
		}	
		
		if ($this->_filter_set_type) 
		{
			if ($this->_filter_set_type == 1 ) {
			  $where[] = ' soq.type = "custom" ' ;	
			}
			 
			if ($this->_filter_set_type == 2) {
			  $where[] = ' soq.type = "random" ' ;
			}
		}
		
		if ($search) {
			$where[] = ' LOWER(soq.title) LIKE '.$this->_db->Quote( '%'.$this->_db->getEscaped( $search, true ).'%', false );
		}
		
		$where = count( $where ) ? ' WHERE ' . implode( ' AND ', $where) : '' ;
		
		$query = 'SELECT soq.*, (SELECT count(setsofquestions_questions.id) 
							     FROM #__jquarks_setsofquestions_questions AS setsofquestions_questions 
							     WHERE setsofquestions_questions.setofquestions_id = soq.id ) AS count,
				  randomSets.nquestions, randomSets.category_id,
				  				(SELECT count(id) 
				  				 FROM #__jquarks_quizzes_setsofquestions AS quizzes_setsofquestions
				  				 WHERE quizzes_setsofquestions.quiz_id = ' . $this ->_id .
								' AND quizzes_setsofquestions.setofquestions_id = soq.id) AS assigned' .
		' FROM #__jquarks_setsofquestions AS soq' .
		' LEFT JOIN #__jquarks_customsets AS customSets ON customSets.set_id = soq.id' .
		' LEFT JOIN #__jquarks_randomsets AS randomSets ON randomSets.set_id = soq.id' .
		$where ;
		
		$total = $this->_getListCount($query) ;	
	
		jimport('joomla.html.pagination') ;
		$this->_pageNavSets = new JPagination($total, $limitstart, $limit) ;
		
		$query .= $orderBy ; // adding the ordering
		 
		$this->_setsOfQuestions = $this->_getList($query, $this->_pageNavSets->limitstart, $this->_pageNavSets->limit) ;		
		
		if($this->_db->getErrorNum()) {
			return false ;
		}
		
		return $this->_setsOfQuestions ;
	}
	
	/**
	 * Get the lists of sets of questions that are assigned to the current quiz
	 * 
	 * @return array
	 */
	function &getAssignedSetsOfQuestions($quizId = null) 
	{
		if($quizId) {
			$this->_quiz->id = $quizId ;
		}
		
		if (empty($this->assignedSetsOfQuestions)) 
		{
			$query = 'SELECT quizzes_setsofquestions.setofquestions_id' .
			' FROM #__jquarks_quizzes_setsofquestions AS quizzes_setsofquestions' .
			' WHERE quizzes_setsofquestions.quiz_id = ' . $this->_quiz->id ;	

			$this->_assignedSetsOfQuestions =  $this->_getlist($query) ;
			if ($this->_db->getErrorNum()) {
				return false ;
			}
		}
		
		return $this->_assignedSetsOfQuestions ;
	}
	
	/**
	 * Get all the registered users
	 * 
	 * @return array
	 */
	function &getUsers($blocked = null) 
	{
		if(empty($this->_users)) 
		{
			$query = 'SELECT id, name, username, email' .
			' FROM #__users' ;
			
			if ($blocked) {
				$query .= ' WHERE block = 0' ; 
			}
			
			$this->_users = $this->_getList($query) ;
			if ($this->_db->getErrorNum()) {
				return false ;
			}
		}
		
		return $this->_users ;
	}
	
	/**
	 * Get the list of user that are assigned to the quiz
	 * 
	 * @return array
	 */
	function getAssignedUsers() 
	{
                $mainframe =& JFactory::getApplication();
		$context					= 'com_jquarks.quiz.users.list.' ;
		$this->filter_assigned		= $mainframe->getUserStateFromRequest( $context.'filter_assigned_users', 'filter_assigned_users', '', 'string') ;
		$search						= $mainframe->getUserStateFromRequest( $context.'search', 'search', '', 'string') ;
		$search 					= JString::strtolower($search) ;
		$this->_filter_order 		= $mainframe->getUserStateFromRequest( $context.'filter_order', 'filter_order', 'name', 'cmd' ) ;
		$this->_filter_order_Dir 	= $mainframe->getUserStateFromRequest( $context.'filter_order_Dir', 'filter_order_Dir', '', 'word') ;
		
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int') ;
	
		$where = array();
		
		$orderBy = '' ;
		
		if ($this->_filter_order_Dir){
			$orderBy 	= ' ORDER BY '.$this->_filter_order.' '. $this->_filter_order_Dir ;
		}
		
		if ($this->filter_assigned) 
		{			
			if ($this->filter_assigned == 'Y') 
			{
				$where[] = ' (SELECT users_quizzes.id 
								FROM #__jquarks_users_quizzes AS users_quizzes
								LEFT JOIN #__jquarks_quizzes AS quizzes ON quizzes.id = users_quizzes.quiz_id
								WHERE quizzes.id = ' . $this->_quiz->id . '
								AND users_quizzes.user_id = users.id ) IS NOT NULL' ;
			} 
			elseif ($this->filter_assigned == 'N') 
			{ 
				$where[] = ' (SELECT users_quizzes.id 
								FROM #__jquarks_users_quizzes AS users_quizzes
								LEFT JOIN #__jquarks_quizzes AS quizzes ON quizzes.id = users_quizzes.quiz_id
								WHERE quizzes.id = ' . $this->_quiz->id . '
								AND users_quizzes.user_id = users.id ) IS NULL' ;
			} 
		}
		
		if ($search) {
			$where[] = ' LOWER(users.name) LIKE '.$this->_db->Quote( '%'.$this->_db->getEscaped( $search, true ).'%', false );
		}
		
		$where = count( $where ) ? ' AND ' . implode( ' AND ', $where) : '' ;
		
		$query = 'SELECT users.*, (SELECT users_quizzes.id 
									FROM #__jquarks_users_quizzes AS users_quizzes
									LEFT JOIN #__jquarks_quizzes AS quizzes ON quizzes.id = users_quizzes.quiz_id
									WHERE quizzes.id = ' . $this->_quiz->id . '
									AND users_quizzes.user_id = users.id ) AS assigned_id, ' .
								  '(SELECT archived 
								  	FROM #__jquarks_users_quizzes AS users_quizzes
								  	WHERE users_quizzes.id = assigned_id) AS archived' .
		' FROM #__users AS users' .
		' WHERE users.block = 0' . 
		$where .
		$orderBy ;
	
		$total = $this->_getListCount($query) ;	
	
		jimport('joomla.html.pagination') ;
		$this->_pageNavUsers = new JPagination($total, $limitstart, $limit) ;
		
		$this->_assignedUsers = $this->_getList($query, $this->_pageNavUsers->limitstart, $this->_pageNavUsers->limit) ;
		if($this->_db->getErrorNum()) {
			return false ;			
		}
		
		return $this->_assignedUsers ;
	}
	
	/**
	 * Generate the drop down list for the list of users and the set of questions assigned status
	 * 
	 * @return array
	 */
	function getLists() 
	{
		$mainframe =& JFactory::getApplication();
                $context                    = 'com_jquarks.quiz.sets.list.' ;
                $assignedSet                = $mainframe->getUserStateFromRequest( $context.'filter_assigned_sets', 'filter_assigned_sets', '', 'string') ;
                $assignedUsers              = $mainframe->getUserStateFromRequest( 'com_jquarks.quiz.users.list.filter_assigned_users', 'filter_assigned_users', '', 'string') ;
                $profiles                   =$mainframe->getUserStateFromRequest( 'com_jquarks.quiz.users.list.filter_profiles', 'filter_profiles', '', 'string') ;
                $this->_filter_set_type     = $mainframe->getUserStateFromRequest( $context.'filter_setType', 'filter_setType', '', 'string') ;
        
		$javascript = ' onchange="document.adminForm.submit();" ' ;
		
		// generating the list of users 		
		if (empty($this->_user)) {
			$this->getUsers(1) ;
		}
		
		$users[] = JHTML::_('select.option', '', '- '.JText::_('SELECT_USER').' -');
		$users[] = JHTML::_('select.option', '-1', JText::_('ALL_REGISTRED'));
		
		foreach( $this->_users as $user) {
			$users[] = JHTML::_('select.option', $user->id, $user->name ) ; 
		}
		
		$this->_lists['users'] = JHTML::_('select.genericlist',	$users, 'filter_user', 'class="inputbox" size="1"' ); // onclick="document.getElementById(\'task\').value=\'\'; document.form.submit();"') ; 
		
		// generating the drop down menu for the sets assignation status
		$status[] = JHTML::_('select.option', '', '- '.JText::_('SELECT_STATUS').' -');
		$status[] = JHTML::_('select.option', 'Y', JText::_('ASSIGNED')) ;
		$status[] = JHTML::_('select.option', 'N', JText::_('NOT_ASSIGNED')) ;
		
		$this->_lists['statusSets'] = JHTML::_('select.genericlist', $status, 'filter_assigned_sets', 'class="inputbox" size="1"' . $javascript , 'value', 'text',  $assignedSet) ;
		$this->_lists['statusUsers'] = JHTML::_('select.genericlist', $status, 'filter_assigned_users', 'class="inputbox" size="1"' . $javascript , 'value', 'text',  $assignedUsers) ;
		
		$setType[] = JHTML::_('select.option', '', '- '.JText::_('SELECT_SET_TYPE').' -');
		$setType[] = JHTML::_('select.option', 1, JText::_('CUSTOM'));
		$setType[] = JHTML::_('select.option', 2, JText::_('RANDOM'));
		$this->_lists['setType'] = JHTML::_('select.genericlist', $setType, 'filter_setType', 'class="inputbox" size="1"' . $javascript , 'value', 'text',  $this->_filter_set_type) ;
		
		$this->_lists['order_Dir']	= $this->_filter_order_Dir ;
		$this->_lists['order']		= $this->_filter_order ;
		
		return $this->_lists ;
	}
	
	function getPageNavUsers() 
	{
		return $this->_pageNavUsers ;
	}
	
	function getPageNavSets()
	{
		return $this->_pageNavSets ;	
	}
	
	/**
	 * For the assigned custom sets of questions get the duplicated questions
	 * 
	 * @param $quizId
	 * @return array of duplicated questions
	 */
	function getDuplicateQuestions($quizId) 
	{
		// building the list of set for the query
		$assignedSets = $this->getAssignedSetsOfQuestions($quizId) ;
		
		$assigned = array() ;
		foreach ($assignedSets as $assignedSet) {
			$assigned[] = $assignedSet->setofquestions_id ;	
		}
		$listSets = implode(",", $assigned );
		
		$query = 'SELECT soq.question_id, q.statement, soq.setofquestions_id, sets.title, (SELECT count(question_id) 
														  FROM #__jquarks_setsofquestions_questions
														  WHERE question_id = soq.question_id 
														  AND setofquestions_id IN (' . $listSets . ')) AS count' .
		' FROM #__jquarks_setsofquestions_questions AS soq' .
		' LEFT JOIN #__jquarks_questions AS q ON soq.question_id = q.id' .
		' LEFT JOIN #__jquarks_setsofquestions AS sets ON soq.setofquestions_id = sets.id' .
		' WHERE setofquestions_id IN (' . $listSets . ')' . 
		' ORDER BY soq.question_id' ;
		
		$this->_db->setQuery($query) ;
		$duplicate = $this->_db->loadAssocList() ;
		if ($this->_db->getErrorNum()) {
			return false ;
		}
		
		// removing non duplicate associations
		foreach ($duplicate as $key => $value) 
		{
			if ($value['count'] == 1 ) {
				unset($duplicate[$key]) ;
			}
		}
		
		return $duplicate ;
	}

	/**
	 * Get all the questions of a specific category that are affected to a given quiz
	 * 
	 * @param $categoryId
	 * @param $quizId
	 * @return array associative
	 */
	function getQuestionsOfCategoryAffectedToQuiz($quizId, $categoryId = NULL) 
	{
		$query = 'SELECT questions.*' .
		' FROM #__jquarks_quizzes_setsofquestions AS quiHaveSoq' .
		' JOIN #__jquarks_setsofquestions AS setsOfQuestions ON setsOfQuestions.id = quiHaveSoq.setofquestions_id' .
		' JOIN #__jquarks_setsofquestions_questions AS soqHaveQ ON soqHaveQ.setofquestions_id = setsOfQuestions.id' .
		' JOIN #__jquarks_questions AS questions ON questions.id = soqHaveQ.question_id' .
		' WHERE quiHaveSoq.quiz_id = ' . $quizId ;
		 
		if ($categoryId) {
			$query .= ' AND questions.category_id = ' . $categoryId ;
		} else {
			$query .= ' AND questions.category_id IS NULL' ;
		}
		
		$this->_db->setQuery($query) ;
		return $this->_db->loadAssocList() ;
	}
	
    /**
     * Get the number of random questions affected to a quiz
     * 
     * @param $quizId
     * @return int
     */
    function getNbrRandomQuestionsAffectedToQuiz($quizId)
    {
        $query = 'SELECT sum(nquestions)' .
        ' FROM #__jquarks_quizzes_setsofquestions AS quizzes_setsofquestions' .
        ' LEFT JOIN #__jquarks_setsofquestions AS setsofquestions ON setsofquestions.id = quizzes_setsofquestions.setofquestions_id' .
        ' JOIN #__jquarks_randomsets AS randomSets ON randomSets.set_id = setsofquestions.id' .
        ' WHERE quizzes_setsofquestions.quiz_id = ' . $quizId ; 
        
        $this->_db->setQuery($query) ;
        return $this->_db->loadResult() ; 
    }
	
	/**
	 * Fetch the list of questions affected to a given quiz
	 * 
	 * @param $quizId
	 * @return array associative
	 */
	function getQuestionsAffectedToQuiz($quizId) 
	{
		$query = 'SELECT soqHaveQ.question_id' .
		' FROM #__jquarks_quizzes_setsofquestions AS quiHaveSoq' .
		' JOIN #__jquarks_setsofquestions AS setsOfQuestions ON setsOfQuestions.id = quiHaveSoq.setofquestions_id' .
		' JOIN #__jquarks_setsofquestions_questions AS soqHaveQ ON soqHaveQ.setofquestions_id = setsOfQuestions.id' .
		' JOIN #__jquarks_questions AS questions ON questions.id = soqHaveQ.question_id' .
		' WHERE quiHaveSoq.quiz_id = ' . $quizId ;
		
		$this->_db->setQuery($query) ;
		return $this->_db->loadAssocList() ;
	}
	
	/**
	 * Get the number of time a user has taken a quiz
	 * 
	 * @param $assignationId
	 * @return int
	 */
	function tookQuiz($assignationId) 
	{
		$query = 'SELECT *' .
		' FROM #__jquarks_quizsession AS quizSession' .
		' WHERE quizSession.affected_id = ' . $assignationId ;

		$this->_db->setQuery($query) ;
		
		$taken = $this->_getListCount($query) ;
		if ($this->_db->getErrorNum()) {
			return false ;
		}
		
		return $taken ;
	}
	
	/**
	 * If a user has already taken a quiz, when he is unassigned from it his assignation is archived
	 * 
	 * @param $assignationId
	 * @return boolean
	 */
	function archiveAssignation($assignationId) 
	{
		$query = 'UPDATE #__jquarks_users_quizzes AS users_quizzes' .
		' SET archived = 1' .
		' WHERE users_quizzes.id = ' . $assignationId ;
							
		$this->_db->execute($query) ;		
		if ($this->_db->getErrorNum()) {
			return false;
		}

		return true ;
	}
	
	/**
	 * Check if the affected set is random and if a set from his category isn't already affected to the quiz
	 * 
	 * @return set of question
	 */
	function randomExist()
	{
		$setOfQuestionsModel   =& JModel::getInstance('setofquestions','JQuarksModel');
		
		$line['quiz_id'] = $this->_id ;
		$line['setofquestions_id'] = JRequest::getVar('setId') ;

		$set['id'] = 0 ;
		
		$setOfQuestion =& JModel::getInstance('setofquestions','JQuarksModel', $line['setofquestions_id']);
		$setOfQuestion->getSetOfQuestions() ;
		
		$set = $setOfQuestionsModel->getRandomSetOfCategoryAffectedToQuiz($line['quiz_id'], $setOfQuestion->_setofquestions->category_id) ;

		if ($setOfQuestion->_setofquestions->type == 'random') {
			return $set ;
		} else {
			return null ;	
		}
			
	}
	
	/**
	 * Get the title of a category
	 * 
	 * @TODO replace this with a method from JQuarksModelCategory
	 * @param $categoryId
	 * @return unknown_type
	 */
	function getCategoryTitle($categoryId) 
	{
		$query = 'SELECT title' .
		' FROM #__jquarks_categories' .
		' WHERE id = ' . $categoryId ;
		
		$this->_db->setQuery($query) ;
		$title = $this->_db->loadResult() ;
		
		if($title) {
			return $title ;
		} else {
			return JText::_('UNCATEGORIZED') ;
		}
	}
	
	/**
	 * Check if the quiz and the questions it contain are coherent
	 * 
	 * @TODO see if it's possible to improve this method
	 * @return array
	 */
	function checkCoherentSets() 
	{
		$categoryModel	 		=& JModel::getInstance('category','JQuarksModel');
		$setOfQuestionsModel	=& JModel::getInstance('setofquestions','JQuarksModel');
		
		$line['quiz_id'] = $this->_id ;
		$line['setofquestions_id'] = JRequest::getVar('setId') ;
		$line['id'] = 0 ;
		
		$setOfQuestions =& JModel::getInstance('setofquestions','JQuarksModel', $line['setofquestions_id']);
		$setOfQuestions->getSetOfQuestions($line['setofquestions_id']) ;

		if ( $setOfQuestions->_setofquestions->type == 'random' )  
		{
			$setStat[0]['category_id'] = $setOfQuestions->_setofquestions->category_id ;
			$setStat[0]['categoryName'] = $this->getCategoryTitle($setStat[0]['category_id']) ;
			
			// getting the number of questions affected to this set
			$setStat[0]['affectedToThisSet'] = (int)$setOfQuestions->_setofquestions->nquestions ;
			
			// getting the number of questions affected to this quiz
			if ($setOfQuestions->_setofquestions->category_id) {
				$setStat[0]['affectedToThisQuiz'] = count($this->getQuestionsOfCategoryAffectedToQuiz($line['quiz_id'], $setOfQuestions->_setofquestions->category_id)) ;
			} else { 
				$setStat[0]['affectedToThisQuiz'] = count($this->getQuestionsOfCategoryAffectedToQuiz($line['quiz_id'])) ;
			}
			
			// getting the total number of questions of this random set's category
			$setStat[0]['totalNumber'] = count($categoryModel->getQuestionsOfCategory('actived', $setOfQuestions->_setofquestions->category_id)) ;
			
			$setStat['coherent'] = true ;
			if ($setStat[0]['affectedToThisQuiz'] + $setStat[0]['affectedToThisSet'] > $setStat[0]['totalNumber'] ) {
				$setStat['coherent'] = false ;
			}

		} 
		elseif ( $setOfQuestions->_setofquestions->type == 'custom' ) 
		{
			$setStat[] = array() ;

			// getting the questions's categories and for each category the number of questions affected to the set
			$query = 'SELECT questions.category_id, count(soqHaveQ.question_id) AS count' .
			' FROM #__jquarks_questions AS questions' .
			' JOIN #__jquarks_setsofquestions_questions AS soqHaveQ ON soqHaveQ.question_id = questions.id' .
			' WHERE soqHaveQ.setofquestions_id = ' . $line['setofquestions_id'] .
			' GROUP BY questions.category_id' ;
			
			$this->_db->setQuery($query) ;
			echo $this->_db->getQuery() ;
			$array = $this->_db->loadObjectList() ;

			if (count($array) > 0) 
			{
				$i = 0 ;
				foreach ($array as $obj) 
				{
					$setStat[$i]["category_id"] = (int)$obj->category_id ;
					$setStat[$i]["categoryName"] =  $this->getCategoryTitle($setStat[$i]["category_id"]) ;
					$setStat[$i]["affectedToThisSet"] = (int)$obj->count ;
				
					$i++ ;
				}
	
				// for each category getting the number of question affected for that category and the total number of question
				foreach ($setStat AS &$category) 
				{
					// getting the number of question affected to this quiz for this category
					$category['affectedToThisQuiz'] = count($this->getQuestionsOfCategoryAffectedToQuiz($line['quiz_id'], $category['category_id'])) ;
				
					// adding the number of question affected to the random set for this category
					$randomSet = $setOfQuestionsModel->getRandomSetOfCategoryAffectedToQuiz($line['quiz_id'], $category['category_id']) ; 
					$category['affectedToThisQuiz'] += $randomSet['nquestions'] ;
					
					// getting the total number of questions available
					$category["totalNumber"] = count($categoryModel->getQuestionsOfCategory('actived', $category['category_id'])) ;
				}
				unset($category) ;
	
				$setStat['coherent'] = true ;
				// checking if one of the category isn't out of bound
				foreach ($setStat AS $category) 
				{
					if ($category['affectedToThisSet'] + $category['affectedToThisQuiz'] > $category['totalNumber']) { 
						$setStat['coherent'] = false ;
					}
				}
				 
			} else {
				$setStat['coherent'] = true ;
			}
		}
		
		return $setStat ;
	}
	
	/**
	 * Assign the selected set to the current quiz
	 *  
	 * @return boolean
	 */
	function assignSet() 
	{
		$this->_quizzes_setsofquestionsTable =& JTable::getInstance('quizzes_setsofquestions', 'Table') ;
		
		$line['quiz_id'] = $this->_id ;
		$line['setofquestions_id'] = JRequest::getVar('setId') ;
		$line['id'] = 0 ;

		// checking whether if the set is empty or not
		$query = 'SELECT soq.id, customSets.id AS custom_id, count(setsofquestions_questions.id) AS custom_count, 
				  randomSets.id AS random_id, randomSets.nquestions AS random_count' .
		' FROM #__jquarks_setsofquestions AS soq' .
		' LEFT JOIN #__jquarks_customsets AS customSets ON soq.id = customSets.set_id' .
		' LEFT JOIN #__jquarks_randomsets AS randomSets ON soq.id = randomSets.set_id' .
		' LEFT JOIN #__jquarks_setsofquestions_questions AS setsofquestions_questions ON setsofquestions_questions.setofquestions_id = soq.id' . 
		' WHERE soq.id = ' . $line['setofquestions_id'] .
		' GROUP BY soq.id' ;
        
		$this->_db->setQuery($query) ;
		$set = $this->_db->loadObject() ;

        
		if ($set->random_count != 0 || $set->custom_count != 0) 
		{
			if (!$this->_quizzes_setsofquestionsTable->save($line)) {
				return false ;
			}
			
			return true ;
		} 
		
		return false;
	}
	
	/**
	 * Unassign the selected set from the current quiz
	 * 
	 * @param $setId
	 * @param $quizId
	 * @return boolean
	 */
	function unassignSet($setId = null, $quizId = null) 
	{
		$this->_quizzes_setsofquestionsTable =& JTable::getInstance('quizzes_setsofquestions', 'Table') ;
		
		if ($quizId) {
			$line['quiz_id'] = $quizId ;
		} else {
			$line['quiz_id'] = $this->_id ;
		}
		
		if ($setId) {
			$line['setofquestions_id'] = $setId ;
		} else {
			$line['setofquestions_id'] = JRequest::getVar('setId') ;
		}
		
		$query = 'SELECT quizzes_setsofquestions.id' .
		' FROM #__jquarks_quizzes_setsofquestions AS quizzes_setsofquestions' .
		' WHERE quizzes_setsofquestions.quiz_id = ' . $line['quiz_id'] .
		' AND quizzes_setsofquestions.setofquestions_id = ' . $line['setofquestions_id'] ; 
		
		$this->_db->setQuery($query) ;
		$id = $this->_db->loadResult() ;
		
		if (!$this->_quizzes_setsofquestionsTable->delete( $id )) 
		{
			$this->setError($this->_db->getErrorMsg() ) ;
			return false ;
		}
		
		return true ;
	}
	
	/**
	 * Unassign the given set from all the quizzes it was affected to
	 * @param $setId
	 * @return boolean
	 */
	function unassignSetFromQuizzes($setId)
	{
		$this->_quizzes_setsofquestionsTable =& JTable::getInstance('quizzes_setsofquestions', 'Table') ;
		//$quizzesModel  =& JModel::getInstance('quizzes','JQuarksModel');
		
		//$quizzes = $quizzesModel->getQuizzesThatHaveSet($setId) ;
		
		$query = 'SELECT *' .
		' FROM #__jquarks_quizzes_setsofquestions AS quiHaveSoq' .
		' WHERE quiHaveSoq.setofquestions_id = ' . $setId ;
		
		$setAssignations = $this->_getList($query) ;
		
		foreach ($setAssignations AS $setAssignation)
		{
			if (!$this->_quizzes_setsofquestionsTable->delete( $setAssignation->id )) 
			{
				$this->setError($this->_db->getErrorMsg() ) ;
				return false ;
			}
		}
		
		return true ;
	}
	
	/**
	 * AssignUser Assign one user to the current quiz
	 * @todo make this check in controller and call a new method assign allRegistredUserThatAreNotBlocked
	 * 
	 * @param $userId
	 * @param $quizId
	 * @return boolean
	 */
        function assignUser($userId = null, $quizId = null) {
            $this->_users_quizzesTable = & JTable::getInstance('users_quizzes', 'Table');

            if ($quizId) {
                $line['quiz_id'] = $this->_id = $quizId;
            } else {
                $line['quiz_id'] = $this->_id;
            }

            if ($userId) {
                $line['user_id'] = $userId;
            } else {
                $line['user_id'] = JRequest::getVar('selected_user');
            }

            if ($line['user_id'] != '') {
                // checking if we must add one or all the registered user
                if ($line['user_id'] == -1) {
                    // affecting all registered user
                    if (!$this->assignUsers()) {
                        return false;
                    }
                } else {
                    // affecting only one user
                    $query = 'SELECT users_quizzes.id, users_quizzes.archived' .
                            ' FROM #__jquarks_users_quizzes AS users_quizzes' .
                            ' WHERE users_quizzes.quiz_id = ' . $line['quiz_id'] .
                            ' AND users_quizzes.user_id = ' . $line['user_id'];

                    $this->_db->setQuery($query);
                    $affect = $this->_db->loadObject();

                    if ($affect == "") { // user notfound we affect him
                        $line['id'] = 0;
                        $line['archived'] = 0;
                    } elseif ($affect != "" && $affect->archived) { // user was archived we unarchieve him
                        $line['id'] = $affect->id;
                        $line['archived'] = 0;
                    }

                    if (!$this->_users_quizzesTable->save($line)) {
                        return false;
                    }
                }

                return true;
            }

            return false;
        }

	/**
	 * Assign all registered user to the quiz
	 * 
	 * @return boolean
	 */
	function assignUsers() 
	{
            $quiz_id = $this->_id ;
            
            $users_ids = JRequest::getVar('uid', array(), '', 'array');

            foreach ($users_ids as $user_id)
            {
                if ( ! $this->assignUser($user_id, $quiz_id))
                {
                    return false;
                }
            }
            return true;
	}
	
	/**
	 * AssignAllUsers make the quiz assigned to all registered and unregistered users
	 * for unregistered user only one affectation is used it's user_id is -1
	 * 
	 * @return boolean
	 */
	function assignAllUsers() 
	{	
		$this->_users_quizzesTable =& JTable::getInstance('users_quizzes', 'Table') ;
		
		//assigning a common unregistered user affectation to the quiz
		$line['quiz_id'] = $this->_id ;
		$line['user_id'] = -1 ;
		
		// checking if the user isn't already affected to this quiz
		$query = 'SELECT *' .
		' FROM #__jquarks_users_quizzes AS users_quizzes' . 
		' WHERE users_quizzes.quiz_id = ' . $line['quiz_id'] .
		' AND users_quizzes.user_id = ' . $line['user_id'] ;
		
		$this->_db->setQuery($query) ;
		$affect = $this->_db->loadObject() ;
		if($this->_db->getErrorNum()){
			return false ;
		}
		
		if ($affect->archived) // case the affectation was archived
		{
			$line['archived'] = 0 ;
			$line['id'] = $affect->id ;	
		} else { // case of a new affectation				
			$line['id'] = 0 ;
		}			
		
		if (!$this->_users_quizzesTable->save($line)) {
			return false ;
		}
		
		// assign registered users
		if(!$this->assignUsers()) {
			return false ;
		}
		
		return true ;
	}
		
	/**
	 * UnassignUser unassign a specific user if the user have already took the quiz the assignation will be archived
	 * 
	 * @return boolean
	 */
	function unassignUser($userId = null, $quizId = null) 
	{
		$this->_users_quizzesTable =& JTable::getInstance('users_quizzes', 'Table') ;
		
		if ($quizId) {
			$line['quiz_id'] = $this->_id = $quizId;
		} else {
			$line['quiz_id'] = $this->_id ;
		}
		
		if ($userId) {
		    $line['user_id'] = $userId ;	
		} else {
            $line['user_id'] = JRequest::getVar('selected_user') ;			
		}

		// getting the id of the record to delete / archive
		$query = 'SELECT users_quizzes.id' .
		' FROM #__jquarks_users_quizzes AS users_quizzes' . 
		' WHERE users_quizzes.quiz_id = ' . $line['quiz_id'] .
		' AND users_quizzes.user_id = ' . $line['user_id'] ;
		
		$this->_db->setQuery($query) ;
				
		$assignationId = $this->_db->loadResult() ;
		if ($this->_db->getErrorNum()) {
			return false ;
		}

        if ($assignationId) {
            $taken = $this->tookQuiz($assignationId) ; // checking if the user has already took the quiz	
        } else {
        	$taken = null ;
        }
		
		if ($taken) {
			$this->archiveAssignation($assignationId) ; // we archive the assignation			
		} 
		else 
		{
			if (!$this->_users_quizzesTable->delete( $assignationId )) // deleting the assignation
			{ 
				$this->setError( $this->_db->getErrorMsg() ) ;
				return false ;
			}	
		}
		
		return true ;
	}
	
         /**
          * Unassign all the user that have been added indiviually to the quiz
          *
          * @return boolean
          */
        function unassignUsers()
        {
            $quiz_id = $this->_id;

            $users_ids = JRequest::getVar('uid', array(), '', 'array');

            foreach ($users_ids as $user_id)
            {
                if ( ! $this->unassignUser($user_id, $quiz_id))
                {
                    return false;
                }
            }
            return true;
        }
	
	/**
	 * UnassignAllUsers remove all the registered user's assignation and the special unregistered user assignation
	 *  
	 * @return boolean
	 */
	function unassignAllUsers() 
	{		 
		$this->_users_quizzesTable =& JTable::getInstance('users_quizzes', 'Table') ;
		
		// unassign the unregistered users		
		$query = 'SELECT *' .
		' FROM #__jquarks_users_quizzes AS users_quizzes' .
		' WHERE user_id = -1'  .
		' AND quiz_id = ' . $this->_id ;
		
		$this->_db->setQuery($query) ;
		$unaffectAll = $this->_db->loadObject() ;

		// checking if it has already been taken
		$taken = $this->tookQuiz($unaffectAll->id) ;
		
		if($taken) {
			$this->archiveAssignation($unaffectAll->id) ; // archieve it
		} 
		else 
		{
			if (!$this->_users_quizzesTable->delete( $unaffectAll->id )) // delete it
			{
				$this->setError( $this->_db->getErrorMsg() ) ;
				return false ;
			}	
		}		
		
		if(!$this->unassignUsers()) { // unassign all registered users
			return false ;
		}
		
		return true ;
	}
	
	/**
	 * Send a notification message to the user
	 * 
	 * @param $quizId
	 * @param $userId
	 * @return boolean
	 */
	function notifyUser($quizId = null, $userId = null) 
	{
		if ($quizId) {
			$this->_id = $quizId ;
		}
		
  		$link = JURI::root() . 'index.php?option=com_jquarks&controller=quiz&id=' . $this->_id ;
  		
  		if ($userId) {
  			$uid = $userId ;
  		} else {
  		    $uid = (int)JRequest::getVar('selected_user') ;// $uids[0] ;	
  		}
		
		$this->_quiz = $this->getQuiz() ;
		
		$user = JFactory::getUser($uid) ;
  		$message =& JFactory::getMailer() ;

  		$message->addRecipient($user->email) ;
  		$message->setSubject( '"' . $this->_quiz->title . '" ' . JText::_('QUIZ_IS_AVAILABLE')) ;
		
  		$notify_message = $this->_quiz->notify_message ;
  		
  		$notify_message = str_replace("[userName]", $user->name, $notify_message) ;
  		$notify_message = str_replace("[quizTitle]", $this->_quiz->title, $notify_message) ;
  		$notify_message = str_replace("[quizDescription]", $this->_quiz->description, $notify_message) ;

  		if ($this->_quiz->publish_down == '0000-00-00 00:00:00') {
  			$notify_message = str_replace("[unpublishDate]", JText::_('Never'), $notify_message) ; 
  		} else {
  			$notify_message = str_replace("[unpublishDate]", $this->_quiz->publish_down, $notify_message) ;
  		}
  		
  		$notify_message = str_replace("[quizLink]", $link, $notify_message) ;

  		$body = $notify_message ;
  		$message->setBody($body) ;

  		$sent = $message->send();
	  	if ($sent != 1) {
	  		return false ;
	  	}
	  	
	  	return true ;
	}
	
	/**
	 * Notify all the users assigned to a quiz
	 * 
	 * @return boolean
	 */
	function notifyUsers()
	{
            $quizId = $this->_id ;

            $users_ids = JRequest::getVar('uid', array(), '', 'array');

            foreach ($users_ids as $userId)
            {
                if ( ! $this->notifyUser($quizId, $userId))
                {
                    return false;
                }
            }
            return true;
	}
	
	function storeNotif() 
	{
		$this->_row =& $this->getTable();
		
		$post = JRequest::get('post') ;
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;
		
		$data['id'] = $cids[0] ;
		$data['published'] = $post['published'] ;
		$data['notify_message'] = $post['notifMess'] ;
	
		return $this->_row->save($data) ;
	}
	
	function store() 
	{		
		$this->_row =& $this->getTable();
		
		$post = JRequest::get('post') ;

		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;
		$details = JRequest::getVar( 'details', array(0), 'post', 'array' ) ;
		
		$data['id']           = $cids[0] ;
		$data['title']        = $post['title'] ;
		$data['description']  = $post['description'] ;
		$data['access_id']    = $post['access'] ;
		$data['published']    = $post['published'] ;
		$data['publish_up']   = $details['publish_up'] ;
		$data['time_limit']   = $post['time_limit'] ;
		$data['show_results'] = isset($post['show_results']) ? 1 : 0 ; 
		
		if (array_key_exists('unique_session', $post) && 1 == $data['access_id']) {
			$data['unique_session'] = 1 ;
		}
		
		if ($data['time_limit'] == '') {
			$data['time_limit'] = null ;
		}

		$data['paginate'] = 'use_pagination=' . $details['paginate'] 
							   . ' use_slide=' . $details['slide'] 
							   . ' question_page=' . $details['questionPage'] ;
		
		if ($details['publish_down'] == JText::_('Never') || $details['publish_down'] == "") {
			$data['publish_down'] = "0000-00-00 00:00:00" ;
		} else {
			$data['publish_down'] = $details['publish_down'] ;
		}
	
		// we have a new quiz 
		$oldAccess = '-1' ;
		
		if ( $data['id'] != 0 ) // case of an old quiz 
		{
			$query = 'SELECT qui.access_id' .
			' FROM #__jquarks_quizzes AS qui' .
			' where qui.id = ' . $data['id'] ;
			
			$this->_db->setQuery($query) ;
			$oldAccess = $this->_db->loadResult() ;
		} 
		
		$data['notify_message'] = $post['notify_message'] ;

		if (!$this->_row->bind($data)) 
		{
			echo $this->_db->getErrorMsg() ;
			return false ;
		} 
		
		if (!$this->_row->store(true)) // true to update the null values
		{
			echo $this->_db->getErrorMsg() ;
			return false ;
		}
	
		if ($oldAccess == -1 ) {
			$this->_id = $this->_db->insertid() ;
		}
		
		// updating the table users_quizzes
		if($data['access_id'] != $oldAccess ) 
		{
			// if this is the first save then oldAccess->access_id will be null
			if ( $data['access_id'] == 0 ) 
			{
				// we are switching to public quiz from registred quiz
				// assign all user registered and not registered (user_id -1)
				if(!$this->assignAllUsers()) {
					return false ;
				}
			} 
			elseif ( $data['access_id'] == 1 ) 
			{
				// we are switching to a non public quiz from a public quiz 
				// unassigning all users
				if(!$this->unassignAllUsers()) {
					return false;
				}
			}
		}
		
		return true ;
	}	
		
}