<?php
/**
 * JQuarks Component Questions Model
 * 
 * @version		$Id: questions.php 87 2010-12-20 16:48:06Z fnaccache $
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


class JQuarksModelQuestions extends JModel 
{

	public $_pageNav ;	
	public $_filter_category ;
	public $_filter_type ;
	public $_filter_order_Dir ;
	public $_filter_order ;
	public $_questions ;
	public $_lists ;
	
	public $_questionsTable ;
	public $_setsofquestions_questionsTable ;
	
	public $_setsofquestionsModel ;
	
	function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Get Questions unsing the filter provided by the user
	 * 
	 * @return array
	 */
	function &getQuestions() 
	{
		$mainframe =& JFactory::getApplication();
		$context			= 'com_jquarks.questions.list.' ;
		$this->_filter_category		= $mainframe->getUserStateFromRequest( $context.'filter_category', 'filter_category', -1, 'string' );
		$this->_filter_type		= $mainframe->getUserStateFromRequest( $context.'filter_type', 'filter_type', 0, 'string' );
		$search				= $mainframe->getUserStateFromRequest( $context.'search', 'search', '', 'string') ;
		$search 			= JString::strtolower($search) ;
                $this->_filter_order    	= $mainframe->getUserStateFromRequest( $context.'filter_order', 'filter_order', 'statement', 'cmd' );
                $this->_filter_order_Dir	= $mainframe->getUserStateFromRequest( $context.'filter_order_Dir', 'filter_order_Dir', '', 'word' );
 	
                $limit      = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int') ;
		
        
                $orderby = '' ;
        
		if($this->_filter_order_Dir) {
			$orderby = ' ORDER BY '.$this->_filter_order.' '.$this->_filter_order_Dir ;
		}
				
		$where = array();

		// we don't show archived questions
		$where[] = '' ;
		
		if ($this->_filter_category == 0) { // case of uncategorized question
			$where[] = ' question.category_id IS NULL' ;
		}
		
		if ($this->_filter_category > 0) {
			$where[] = ' question.category_id = ' . (int) $this->_filter_category;
		}
		
		if ($this->_filter_type) {
			$where[] = ' question.type_id = ' . (int) $this->_filter_type;
		}
		
		if ($search) {
			$where[] = 'LOWER(question.statement) LIKE '.$this->_db->Quote( '%'.$this->_db->getEscaped( $search, true ).'%', false );
		}
		
		$where		= count( $where ) ? ' ' . implode( ' AND ', $where ) : '' ;
		
		// getting the list of questions that are not archived
		if (empty( $this->_questions )) 
		{
			$query	= 'SELECT question.id, question.description, question.statement, category.title AS category, type.title AS type' .
			' FROM `#__jquarks_questions` AS question' .
		//	' LEFT JOIN (`#__jquarks_types` AS t, `#__jquarks_categories` AS c) ON (t.id = q.type_id AND c.id = q.category_id)' .
			' LEFT JOIN `#__jquarks_types` AS type ON type.id = question.type_id' .  
			' LEFT JOIN `#__jquarks_categories` AS category ON category.id = question.category_id' . 
			' WHERE question.archived = 0' .
			$where .
			$orderby ;

			$total = $this->_getListCount($query) ;
			
			// creating the pagination according to the number of questions
			jimport('joomla.html.pagination') ;
			$this->_pageNav = new JPagination( $total, $limitstart, $limit ) ;
			
			$this->_db->setQuery($query) ;
                        //echo $this->_db->getQuery() ;
			
			$this->_questions = $this->_getList( $query, $this->_pageNav->limitstart, $this->_pageNav->limit );
			if ($this->_db->getErrorNum()) {
				return false;
			}
		}
		return $this->_questions ;
	}
	
	/**
	 * Create a drop down list for the categories and types of questions
	 * 
	 * @return array
	 */
	function &getLists() 
	{
		$javascript = ' onchange="document.adminForm.submit();" ';
		
		// categories filter get the list of categories from the table categories and create a select box with them to be used as filter
		$query = 'SELECT c.id, c.title' .
		' FROM #__jquarks_categories AS c' ;
		
		$category[] = JHTML::_('select.option', '-1', '- '.JText::_('SELECT_CATEGORY'). ' -');
		$category[] = JHTML::_('select.option', '0', JText::_('UNCATEGORIZED'));
		$this->_db->setQuery($query) ;
		
		foreach( $this->_db->loadObjectList() as $obj ) {
			$category[] = JHTML::_('select.option', $obj->id, $obj->title ); 
		}
		
		$this->_lists['category'] = JHTML::_('select.genericlist',	$category, 'filter_category', 'class="inputbox" size="1"' . $javascript, 'value', 'text', (int) $this->_filter_category );
		
		// type filter
		$query = 'SELECT *' .
		' FROM #__jquarks_types' ;
		
		$type[] = JHTML::_('select.option', '0', '- '.JText::_('SELECT_TYPE').' -');
		
		$this->_db->setQuery($query);
		//$types = array_merge($types, $db->loadObjectList());
		$objects = $this->_db->loadObjectList() ; 
		if($this->_db->getErrorNum()) {
			return false ;
		}
		
		foreach( $objects as $obj ) {
			$type[] = JHTML::_('select.option',  $obj->id, $obj->title );
		}
		
		$this->_lists['type'] = JHTML::_('select.genericlist',	$type, 'filter_type', 'class="inputbox" size="1"' . $javascript, 'value', 'text', (int) $this->_filter_type );
		
		$this->_lists['order'] = $this->_filter_order ;
		$this->_lists['order_Dir'] = $this->_filter_order_Dir ;
		
		return $this->_lists ;
	}
	
	/**
	 * 
	 * @return object JNavigation
	 */
	function getPageNav() 
	{
		return $this->_pageNav ;
	}
	
	/**
	 * Check if a given question has already been answered by a user
	 *  
	 * @param $questionId
	 * @return boolean
	 */
	function questionAnswered($questionId) 
	{
		$query = 'SELECT *' .
		' FROM #__jquarks_quizzes_answersessions AS quizSessAns' .
		' WHERE quizSessAns.question_id = ' . $questionId ;
		
		$this->_db->setQuery($query) ;
		
		$answered = $this->_getListCount($query) ;
		if ($this->_db->getErrorNum()) {
			return false ;
		}

		return $answered > 0 ? true : false ;
	}
	
	/**
	 * Archive the question passed on as parameter and all the proposition that are affected to it
	 * 
	 * @todo change the update query with build in _db->save() 
	 * @param $cid int
	 * @return boolean
	 */
	function archiveQuestion($cid) 
	{
		$this->_questionsTable =& JTable::getInstance('question', 'Table') ;
		
		// archiving the question	
		$data['id'] = $cid ;
		$data['archived'] = 1 ;
		
		if(!$this->_questionsTable->save($data)) {
			return false ;
		}
		
		// archiving the propositions		
		$query = 'UPDATE #__jquarks_propositions AS p' .
		' SET archived = 1' .
		' WHERE p.question_id = ' . $cid ;
			
		$this->_db->execute($query) ;		
		if ($this->_db->getErrorNum()) {
			return false;
		}
		
		return true ;
	}	
	
	/**
	 * Check for all the questions selected to be deleted if among them some wouldn't cause a random set to 
	 * become incoherent (not enough questions available)
	 * 
	 * @param $cids ids of questions to be deleted
	 * @return list of all affected random sets
	 */
	function checkRandomDependencies($questionsId) 
	{
		foreach ($questionsId as $questionId) 
		{
			// getting for the current questions it's category and the number of question available in it's category
			$query = 'SELECT q.category_id, (select count(id)
											  FROM #__jquarks_questions
											  WHERE category_id = q.category_id
											  AND archived = 0) AS count' .
			' FROM #__jquarks_questions AS q' .
			' WHERE q.id = ' . $questionId ;
			
			$this->_db->setQuery($query) ;
			$categoryStat = $this->_db->loadObject() ; 
			
			// fetching from the list of the random sets the sets affected by this deleting
			$query = 'SELECT *, soq.title' .
			' FROM #__jquarks_randomsets' .
			' LEFT JOIN #__jquarks_setsofquestions AS soq ON soq.id = set_id' .
			' WHERE category_id = ' . $categoryStat->category_id .
			' AND nquestions > ' . ($categoryStat->count - 1) ;
			
			$this->_db->setQuery($query) ;
			$incoherentRandomSets = $this->_db->loadObjectList() ;
		}
		
		return $incoherentRandomSets ;
	}
	
	/**
	 * Check for all the questions selected to be deleted if among them some wouldn't cause custom sets that are affected to quizzes to become empty
	 * 
	 * @TODO change this so it only detect custom sets affected to quizzes
	 * @param $cids ids of questions to be deleted
	 * @return list of empty custom sets 
	 */
	function checkCustomDependencies($questionsId) 
	{
		$questionAffectSets = null ;
		foreach ($questionsId AS $questionId) 
		{
			// getting the id of the custom sets where this questions is affected 
			$query = 'SELECT setsofquestions_questions.setofquestions_id, (SELECT count(setofquestions_id)
												FROM #__jquarks_setsofquestions_questions
												WHERE setofquestions_id = setsofquestions_questions.setofquestions_id) AS count, soq.title' .
			' FROM #__jquarks_setsofquestions_questions AS setsofquestions_questions' .
			' LEFT JOIN #__jquarks_setsofquestions AS soq ON soq.id = setofquestions_id' .
			' JOIN #__jquarks_quizzes_setsofquestions AS quizzes ON quizzes.setofquestions_id = soq.id' .
			' WHERE setsofquestions_questions.question_id = ' . $questionId .
			' GROUP BY setofquestions_id' ;

			$this->_db->setQuery($query) ;
			echo $this->_db->getQuery() ; 
			$questionAffectSets[] = $this->_db->loadObjectList() ;
		}
                
		$incoherentCustomSets = null ;
		foreach ( $questionAffectSets AS $sets ) 
		{
			foreach ($sets AS $set) 
			{
				if ($set->count == 1) {
					$incoherentCustomSets[] = $set->setofquestions_id ;
				}	
			}
		}

		return $incoherentCustomSets ;
	}
	
	/**
	 * Delete the selected question(s) or archive it if it's already have been answered once
	 * if a question is archived all it's propositions are also archieved
	 * 
	 * @param $questionsIds
	 * @return boolean
	 */
	function delete($questionsIds) 
	{
		$this->_questionsTable =& JTable::getInstance('question', 'Table') ;
		$this->_setsofquestions_questionsTable =& JTable::getInstance('setsofquestions_questions', 'Table') ;
		
		foreach($questionsIds as $questionId) 
		{
			// checking if the question has been answered			
			$answered = $this->questionAnswered($questionId) ;
			
			if ($answered) {
				$this->archiveQuestion($questionId) ;				
			} else 
			{
				// deleting the questions
				if (!$this->_questionsTable->delete( $questionId ))
				{
					$this->setError( $this->_db->getErrorMsg() ) ;
					return false ;
				}	
				
				// if the question is to be archived affectations have to be deleted
				$this->_setsofquestions_questionsTable ;
				$query = 'SELECT id' .
				' FROM #__jquarks_setsofquestions_questions AS setsofquestions_questions' .
				' WHERE setsofquestions_questions.question_id = ' . $questionId ;
				
				//$this->_db->setQuery($query) ;
				$assignationToDelete = $this->_getList($query) ;
				
				foreach ($assignationToDelete as $assignation) 
				{
					if (!$this->_setsofquestions_questionsTable->delete( $assignation->id ))
					{
							$this->setError( $this->_db->getErrorMsg() ) ;
							return false ;
					}
				}
			}
			
		}		
		return true ;
	}	
	
}