<?php
/**
 * JQuarks Component Setsofquestions Model
 * 
 * @version		$Id: setsofquestions.php 59 2010-03-02 08:40:27Z fnaccache $
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


class JQuarksModelSetsofquestions extends JModel 
{

	/**
	 * 
	 * @var array
	 */
	public $_setsofquestions;
	
	public $_setsofquestionsTable ;
	public $_setsofquestions_questionsTable ;
	
	public $_pageNav ;
	public $_filter_order ;
	public $_filter_order_Dir ;
	
	function __construct() 
	{
		parent::__construct() ;
	}
		
	/**
	 * Get the list of sets of questions according to the users filters
	 * 
	 * @return array
	 */
	function getSetsofquestions() 
	{
		if (empty( $this->_setsofquestions )) 
		{
			$mainframe =& JFactory::getApplication();
			$context 	= 'com_jquarks.setsofquestions.list.' ;
			$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
			$limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int') ;

			$this->_filter_order    	= $mainframe->getUserStateFromRequest( $context.'filter_order', 'filter_order', 'title', 'cmd' );
        	$this->_filter_order_Dir	= $mainframe->getUserStateFromRequest( $context.'filter_order_Dir', 'filter_order_Dir', '', 'word' ); 			
			
			$orderby = '' ;
	        
			if($this->_filter_order_Dir) {
				$orderby = ' ORDER BY '.$this->_filter_order.' '.$this->_filter_order_Dir ;
			}
        	
			$query = ' SELECT soq.*, (SELECT count(custom.id) 
									  FROM #__jquarks_customsets AS custom 
									  WHERE set_id = soq.id) AS count, 
					   randomSets.nquestions, randomSets.category_id' .
			' FROM #__jquarks_setsofquestions AS soq' .
			' LEFT JOIN #__jquarks_customsets AS customSets ON customSets.set_id = soq.id' .
			' LEFT JOIN #__jquarks_randomsets AS randomSets ON randomSets.set_id = soq.id' .
			$orderby ;
			
			$total = $this->_getListCount($query) ;	
			
			jimport('joomla.html.pagination') ;
			$this->_pageNav = new JPagination( $total, $limitstart, $limit ) ;
			
			$this->_db->setQuery($query) ;			
			$this->_setsofquestions = $this->_getList( $query, $this->_pageNav->limitstart, $this->_pageNav->limit );
			if ($this->_db->getErrorNum()) {
				return false;
			}
			
			foreach ($this->_setsofquestions as $setofquestions) 
			{
				$query = 'SELECT count(setsofquestions_questions.id) AS count' .
				' FROM #__jquarks_setsofquestions_questions AS setsofquestions_questions' .
				' JOIN #__jquarks_questions AS questions ON questions.id = setsofquestions_questions.question_id' .
				' WHERE setsofquestions_questions.setofquestions_id = ' . $setofquestions->id .
				' AND questions.archived = 0' ;	
				
				$this->_db->setQuery($query) ;
				$setofquestions->count = $this->_db->loadResult() ;
				if ($this->_db->getErrorNum()) {
					return false;
				}
			}
		}
		return $this->_setsofquestions ;
	}	
	
	/**
	 * Get the quizzes that the set of question is affected to
	 *  
	 * @return object affected quizzes
	 */
	function getAffectedQuizzes() 
	{
		$this->_affectedQuizzes = "" ;
		
		foreach ($this->_setsofquestions AS $set) 
		{
			$query = 'SELECT qui.id, qui.title, qui.published' .
			' FROM #__jquarks_quizzes_setsofquestions AS quizzes_setsofquestions' .
			' LEFT JOIN #__jquarks_quizzes AS qui ON quizzes_setsofquestions.quiz_id = qui.id' .
			' LEFT JOIN #__jquarks_setsofquestions AS soq ON quizzes_setsofquestions.setofquestions_id = soq.id' .
			' WHERE soq.id = ' . $set->id ;
			
			$this->_db->setQuery($query);
			$this->_affectedQuizzes[$set->id] = $this->_db->loadObjectList() ; 
		}

		return $this->_affectedQuizzes ;
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
	 * Delete the selected Set of Questions
	 * 
	 * @param $setId int
	 * @return boolean
	 */
	function delete($setId) 
	{
		$this->_setsofquestionsTable =& JTable::getInstance('setofquestions', 'Table') ;
		
		if (!$this->_setsofquestionsTable->delete( $setId ))
		{
			$this->setError( $this->_db->getErrorMsg() ) ;
			return false ;
		}

		return true ;
	}		
	
}