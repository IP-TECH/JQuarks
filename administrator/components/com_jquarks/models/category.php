<?php
/**
 * JQuarks Component Category Model
 * 
 * @version		$Id: category.php 59 2010-03-02 08:40:27Z fnaccache $
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


class JQuarksModelCategory extends JModel 
{
	
	/**
	 * 
	 * @var int
	 */
	public $_id ;
	
	/**
	 * 
	 * @var array
	 */
	public $_category ;
	
	/**
	 * 
	 * @var Object Instance of TableCategory
	 */
	public $_row ;
	
	public function __construct() 
	{
		parent::__construct();		
	    
		// Initialised by the id of the current selected category if existant
		$array = JRequest::getVar('cid', 0, '', 'array') ;	 	
	 	$this->setId((int)$array[0]);
	}
			
	/**
	 * Set the identifier
	 *  
	 * @param $id int category identifier
	 * @return unknown_type
	 */
    function setId($id)
    {
        $this->_id          = $id;
        $this->_category    = null;
    }
	
    
	/**
	 * Get the selected category or create a new one
	 * 
	 * @return object
	 */
	public function &getCategory($catId = null) 
	{
		if ($catId) {
			$this->setId($catId) ;
		}
		
		// case of an existing category
		if (empty($this->_category)) 
		{
			$query = 'SELECT *'.
				' FROM #__jquarks_categories'.
				' WHERE id = '. $this->_id ;
			
			$this->_db->setQuery($query) ;
			$this->_category = $this->_db->loadObject() ;
		}
		
		if (!$this->_category)// case of a new category
		{
			$this->_category = new stdClass() ;
			$this->_category->id             = 0 ;
			$this->_category->title          = '' ; 
			$this->_category->description    = '' ;
		}
		
		return $this->_category ;
	}
	
	/**
	 * Get the list of questions for the given category
	 *  
	 * @param $categoryId
	 * @param $archived may take 'archived' or 'actived'
	 * @return array the questions belonging to the category
	 */
	function getQuestionsOfCategory($status = NULL, $categoryId = NULL)
	{
		$query = 'SELECT *' .
		' FROM #__jquarks_questions AS questions' .
		' LEFT JOIN #__jquarks_categories AS categories ON categories.id = questions.category_id' ;
		
		switch($status) 
		{
			case 'archived' : 
				$query .= ' WHERE questions.archived = 1' ;
				break ;
				
			default :
				$query .= ' WHERE questions.archived = 0' ;
		}
		
		if ($categoryId) {
		    $query .= ' AND questions.category_id = ' . $categoryId ;
		} else {
			$query .= ' AND questions.category_id IS NULL' ;
		}
		
		return $this->_getList($query) ;
	}
	
	public function getId() 
	{
		return $this->_id ;
	}
	
    /**
	 * Create a new category
	 * 
	 * @todo quite the same as store keep only one of the two
	 * @return array
	 */
	function addCategory() 
	{
		$this->_categoriesTable =& JTable::getInstance('category', 'Table') ;
		
		$data = JRequest::get('GET') ;
		
		$category['id'] = 0 ;
		$category['title'] = $data['title'] ;

		if (!$this->_categoriesTable->save($category)) {
			return false ;
		}
		
		$category['id'] = $this->_db->insertid();
		
		return $category ;
	}
	
	/**
	 * Remove a category
	 * 
	 * @return array
	 */
	function removeCategory() 
	{
		$this->_categoriesTable =& JTable::getInstance('category', 'Table') ;
		
		$data = JRequest::get('GET') ;
		
		$category['id'] = $data['id'] ;

		if (!$this->_categoriesTable->delete($category['id']))
			return false ;
		
		return $category ;
	}
	
	/**
	 * Storing the category
	 *  
	 * @param $category an array containing prporety of the category
	 * @return boolean
	 */
	public function store() 
	{
		$this->_row =& $this->getTable();
		
		$category = JRequest::get( 'post' ) ;
		
		if(!$this->_row->save($category))
			return false ;
			
		if ($category['id']) {
			$this->_id = $category['id'] ;
		} else {
			$this->_id = $this->_db->insertid() ; 	
		}
		
		return true ;
	}	
	
}