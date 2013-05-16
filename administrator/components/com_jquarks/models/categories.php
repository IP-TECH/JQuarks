<?php
/**
 * JQuarks Component Categories Model
 * 
 * @version		$Id: categories.php 59 2010-03-02 08:40:27Z fnaccache $
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


class JQuarksModelCategories extends JModel 
{

	
	public $_categories ;

	public $_categoriesTable ;
	
	public $_pageNav ;
	
	public $_lists ;
	public $_filter_order ;
	public $_filter_order_Dir ;
	
		
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Get the list of Categories
	 *  
	 * @return array
	 */
	public function getCategories() 
	{
		
		if (empty( $this->_categories )) 
		{

			$mainframe =& JFactory::getApplication();
			$context 	= 'com_jquarks.categories.list.' ;

			$this->_filter_order    	= $mainframe->getUserStateFromRequest( $context.'filter_order', 'filter_order', 'title', 'cmd' );
        	$this->_filter_order_Dir	= $mainframe->getUserStateFromRequest( $context.'filter_order_Dir', 'filter_order_Dir', '', 'word' );
 				
			$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
			$limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int') ;
			
			$orderby = '' ;
	        
			if($this->_filter_order_Dir) {
				$orderby = ' ORDER BY '.$this->_filter_order.' '.$this->_filter_order_Dir ;
			}
			
			$query = ' SELECT *' .
			' FROM #__jquarks_categories' . 
			$orderby ;
		
			$total = $this->_getListCount($query, $limitstart, $limit) ;	
 
			
			jimport('joomla.html.pagination') ;
			$this->_pageNav = new JPagination( $total, $limitstart, $limit ) ;
			
			$this->_db->setQuery($query) ;			
			$this->_categories = $this->_getList( $query, $this->_pageNav->limitstart, $this->_pageNav->limit );
			if ($this->_db->getErrorNum()) {
				return false;
			}
		}
		
		return $this->_categories ;		
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
	 * Get the random sets of the given category
	 * 
	 * @param $quizId
	 * @param $categoryId
	 * @return array associative
	 */
	function getRandomSetsOfCategory($categoryId = NULL)
	{
		$query = 'SELECT randomSets.*' .
		' FROM #__jquarks_setsofquestions AS setsOfQuestions' .
		' JOIN #__jquarks_randomsets AS randomSets ON randomSets.set_id = setsOfQuestions.id' ;
		
		if ($categoryId) {
			$query .= ' WHERE randomSets.category_id = ' .$categoryId ;	
		} else { // case of uncategorized random set
			$query .= ' WHERE randomSets.category_id IS NULL' ;
		} 
		
		$this->_db->setQuery($query) ;
		return $this->_db->loadAssocList() ;
	}
	
	/**
	 * Delete the selected categories
	 * 
	 * @return boolean
	 */
	function delete() 
	{
		$categoriesIds = JRequest::getvar('cid', 0, '', 'array') ;
        // TODO add a check on the array to verify that all elements are int
		
		$quizzesModel =& JModel::getInstance('quizzes','JQuarksModel');
        $quizModel    =& JModel::getInstance('quiz','JQuarksModel');
        
		$this->_categoriesTable =& JTable::getInstance('category', 'Table');
		$this->_setsofquestionsTable =& JTable::getInstance('setofquestions', 'Table') ;
		
		$notice = '' ;
		
		foreach($categoriesIds as $categoryId) 
		{
			$randomSets = $this->getRandomSetsOfCategory($categoryId) ;
			foreach ($randomSets AS $randomSet)
			{
				 
				$quizzesThatHaveThisSet = $quizzesModel->getQuizzesThatHaveSet($randomSet['set_id']) ;
				
				// deleting all random sets of questions of this category
				if (!$this->_setsofquestionsTable->delete( $randomSet['set_id'] ))
				{
					$this->setError( $this->_db->getErrorMsg() ) ;
					return false ;
				}
				
				// unpublishing the quizzes that became empty after deleting the set
			    foreach ($quizzesThatHaveThisSet AS $quiz)
			    {
			    	$customQuestionsAffectedToQuiz = $quizModel->getQuestionsAffectedToQuiz($quiz['id']) ;
			    	
				    $published = $quizModel->getQuiz($quiz['id']) ;
	                $published = (int)$published->published ;
	                                        
	                if ( $published && !count($customQuestionsAffectedToQuiz) && !$quizModel->getNbrRandomQuestionsAffectedToQuiz($quiz['id']) ) 
	                {
	                    if ($quizzesModel->unPublish($quiz['id'])) {
	                        $notice = JText::_('EMPTY_QUIZZES_HAVE_BEEN_UNPUBLISHED') ;
	                    } else {
	                    	$notice = JText::_('ERROR_UNPUBLISHING_EMPTY_QUIZZES') ;
	                    }
	                }
			    }
			}

			if ($notice) {
				JError::raiseNotice('', $notice) ;
			}
			
			if (!$this->_categoriesTable->delete( $categoryId ))
			{
				$this->setError( $this->_db->getErrorMsg() ) ;
				return false ;
			}
		}
		return true ;
	}
	
}