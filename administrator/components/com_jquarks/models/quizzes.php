<?php
/**
 * JQuarks Component Quizzes Model
 * 
 * @version		$Id: quizzes.php 70 2010-04-26 08:56:04Z fnaccache $
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


class JQuarksModelQuizzes extends JModel 
{
	public $_quizzes ;
	public $_quizzesTable ;
	public $_pageNav ;
	public $_filter_order ;
	public $_filter_order_Dir ;
	
	function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Retrieves the quizzes according to the user's filters
	 * 
	 * @return array Array of objects containing the data form the database
	 */
	function getQuizzes() 
	{
		if (empty( $this->_quizzes )) 
		{
                        $mainframe =& JFactory::getApplication();
			$context 	= 'com_jquarks.quizzes.list.' ;
			$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
			$limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int') ;
			
			$this->_filter_order    	= $mainframe->getUserStateFromRequest( $context.'filter_order', 'filter_order', 'title', 'cmd' );
        	$this->_filter_order_Dir	= $mainframe->getUserStateFromRequest( $context.'filter_order_Dir', 'filter_order_Dir', '', 'word' );
			
			$orderby = '' ;
	        
			if($this->_filter_order_Dir) {
				$orderby = ' ORDER BY '.$this->_filter_order.' '.$this->_filter_order_Dir ;
			}
                        $query = ' SELECT quizzes.*' .
                                     ' FROM #__jquarks_quizzes AS quizzes' .
                                     $orderby ;
			
			$total = $this->_getListCount($query) ;	
			
			jimport('joomla.html.pagination') ;
			$this->_pageNav = new JPagination( $total, $limitstart, $limit ) ;
			
			$this->_db->setQuery($query) ;		
                        $nbr = $this->_db->getErrorNum();
			$this->_quizzes = $this->_getList( $query, $this->_pageNav->limitstart, $this->_pageNav->limit );
			if ($this->_db->getErrorNum()) {
				return false;
			}
		}
		
		return $this->_quizzes ;
	}
	
	/**
	 * Get the list of all the sets that are affected to the current quiz
	 * 
	 * @return object list of sets of questions 
	 */
	function getAffectedSets()
	{
		$this->_affectedSets = "" ;
		
		foreach ($this->_quizzes AS $quiz) 
		{
			$query = 'SELECT soq.id, soq.title' .
			' FROM #__jquarks_quizzes_setsofquestions AS quizzes_setsofquestions' .
			' LEFT JOIN #__jquarks_quizzes AS qui ON quizzes_setsofquestions.quiz_id = qui.id' .
			' LEFT JOIN #__jquarks_setsofquestions AS soq ON quizzes_setsofquestions.setofquestions_id = soq.id' .
			' WHERE qui.id = ' . $quiz->id ;
			
			$this->_db->setQuery($query);
			$this->_affectedSets[$quiz->id] = $this->_db->loadObjectList() ; 
		}

		return $this->_affectedSets ;
	}
	
	/**
	 * Get the list of quizzes that have the set assigned to them
	 * 
	 * @param $setId
	 * @return array associative
	 */
	function getQuizzesThatHaveSet($setId) {
		
		$query = 'SELECT quizzes.*' .
		' FROM #__jquarks_quizzes_setsofquestions AS quiHaveSoq' .
		' JOIN #__jquarks_quizzes AS quizzes ON quizzes.id = quiHaveSoq.quiz_id' .
		' WHERE quiHaveSoq.setofquestions_id = ' . $setId ;
		
		$this->_db->setQuery($query) ;

 		return $this->_db->loadAssocList() ;
	}
	
	function getPageNav() 
	{
		return $this->_pageNav ;
	}
	
	function getLists() 
	{
		$this->_lists['order'] = $this->_filter_order ;
		$this->_lists['order_Dir'] = $this->_filter_order_Dir ;
		
		return $this->_lists ;
	}
	
	/**
	 * Publish the quiz
	 * 
	 * @param $quizId
	 * @return boolean
	 */
	function publish($quizId) 
	{
        $this->_row =& JTable::getInstance('quiz', 'Table') ;

		$data['id'] = $quizId ;

		$query = 'SELECT *' .
		' FROM #__jquarks_quizzes AS qui' .
		' WHERE qui.id = ' . $data['id'] ;

		$this->_db->setQuery($query) ;
		$data = $this->_db->loadAssoc() ;

		$data['published'] = 1 ;
	
		// getting the list of setsofquestions assigned to this quiz
		$query = 'SELECT quizzes_setsofquestions.setofquestions_id' .
		' FROM #__jquarks_quizzes_setsofquestions AS quizzes_setsofquestions' .
		' WHERE quizzes_setsofquestions.quiz_id = ' . $data['id'] ;
		
		$this->_db->setQuery($query); 
		$sets = $this->_db->loadResultArray() ;

        foreach($sets AS $set) :
			// checking if the current set contain a question
			$query = 'SELECT randomSets.nquestions AS randomCount, count(setsofquestions_questions.id) AS customCount' .
			' FROM #__jquarks_setsofquestions AS soq' .
			' LEFT JOIN #__jquarks_customsets AS customSets ON customSets.set_id = soq.id' .
			' LEFT JOIN #__jquarks_randomsets AS randomSets ON randomSets.set_id = soq.id' .
			' LEFT JOIN #__jquarks_setsofquestions_questions AS setsofquestions_questions ON setsofquestions_questions.setofquestions_id = customSets.set_id' .
			' WHERE soq.id = ' . $set .
            ' GROUP BY randomCount';
			
			$this->_db->setQuery($query) ;
			$count = $this->_db->loadObject() ; 

			if ($count->randomCount || $count->customCount)
			{
                if ( ! $this->_row->save($data)) {
					return false ;
				}
				return true ;
			}
		endforeach;

        return false;
    
	}

    
	/**
	 * Unpublish the quiz
	 * 
	 * @param $quizId
	 * @return boolean
	 */
	function unPublish($quizId) 
	{
		$this->_row =& JTable::getInstance('quiz', 'Table') ;
		
		$data['id'] = $quizId ;
		
		$query = 'SELECT *' .
		' FROM #__jquarks_quizzes AS qui' .
		' WHERE qui.id = ' . $data['id'] ;
		
		$this->_db->setQuery($query) ;
		$data = $this->_db->loadAssoc() ;
		
		$data['published'] = 0 ;
		
		if (!$this->_row->save($data)) {
			return false ;
		}
		
		return true ;
	}
	
	/**
	 * Delete the list of provided quizzes
	 *  
	 * @param $quizzesIds array of int
	 * @return boolean
	 */
	function delete($quizzesIds) 
	{
		$this->_row =& JTable::getInstance('quiz', 'Table') ;

		foreach($quizzesIds as $quizId) 
		{
			if (!$this->_row->delete( $quizId ))
			{
				$this->setError( $this->_db->getErrorMsg() ) ;
				return false ;
			}
		}
		
		return true ;
	}
	
}