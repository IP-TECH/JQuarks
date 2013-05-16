<?php
/**
 * JQuarks Component Question Model
 * 
 * @version	$Id: question.php 93 2010-12-30 09:11:57Z fnaccache $
 * @author	IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Models
 * @link	http://www.iptechinside.com/labs/projects/show/jquarks
 * @since	0.1
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


class JQuarksModelQuestion extends JModel 
{
	
	public $_id ;
	public $_question ;
	public $_categories ;
	public $_propositions ;
	public $_lists ; 
	public $_propositionsTable ;
	public $_questionsTable ;
	
	function __construct() 
	{
		parent::__construct();
			   
		$array = JRequest::getVar('cid', 0, '', 'array') ;
	 	$this->_id = (int)$array[0] ;		
	}
	
	/**
	 * Get the selected question
	 * 
	 * @return object
	 */
	function &getQuestion() 
	{		
		if (empty($this->_question)) 
		{
			$query = 'SELECT q.*'.
				' FROM #__jquarks_questions AS q'.
				' WHERE q.id='. $this->_id ;
			
			$this->_db->setQuery($query) ;
			$this->_question = $this->_db->loadObject() ;
		}
		
		if (!$this->_question) 
		{

			$this->_question = new stdClass() ;
			$this->_question->id = 0 ;
                        $this->_question->description = '';
			$this->_question->statement = '' ;
			$this->_question->category_id = 0 ;
			$this->_question->type_id = 0 ; 
		}	
		
		return $this->_question ;
	}
	
	function getId() 
	{
		return $this->_id ;
	}

        public function setId($id)
        {
            $this->_id = (int)$id;
        }
	
	/**
	 * Get the propositions for the retrieved question
	 * 
	 * @return array of propositions
	 */
	function &getPropositions() 
	{		
		if ($this->_question) 
		{
			$query = 'SELECT *' .
			' FROM #__jquarks_propositions' .
			' WHERE question_id = ' . $this->_id . 
			' order by id ASC' ;

			$this->_db->setQuery($query) ;
			$this->_propositions = $this->_db->loadObjectList();
		}

		return $this->_propositions ;
	}
		
	/**
	 * Creating a drop down menu for categories and types
	 * 
	 * @return array
	 */
	function &getLists() 
	{
		if (empty($this->_categories)) 
		{
			$query = 'SELECT *' .
			' FROM #__jquarks_categories' ;
			
			$this->_db->setQuery($query) ;
			$categoriesList = $this->_db->loadObjectList();
		}		
		
		// building the drop down list for categories
		$categories[] = JHTML::_('select.option', '0', '- '. JText::_('SELECT_CATEGORY') . ' -') ;
		foreach( $categoriesList as $obj) {
			$categories[] = JHTML::_('select.option', $obj->id, $obj->title );			
		}
		
   		if(!$this->_question->category_id) {
   			// new question
   			$this->_lists['categories'] = JHTML::_('select.genericlist',	$categories, 'category_id', 'class="inputbox" size="1" '  ) ;	
   		} else {
   			$this->_lists['categories'] = JHTML::_('select.genericlist',	$categories, 'category_id', 'class="inputbox" size="1" ', 'value', 'text', (int) $this->_question->category_id) ;
   		}
		
   		if (empty($this->_types)) 
   		{
			$query = 'SELECT *' .
			'FROM #__jquarks_types' ;
			
			$this->_db->setQuery($query) ;
			$typesList = $this->_db->loadObjectList() ;	
		}		
   		
   		// building the drop down list for types
   		$types[] = JHTML::_('select.option', '0', '- '. JText::_('SELECT_TYPE') . ' -') ;
   		foreach( $typesList as $obj) {
   			$types[] = JHTML::_('select.option', $obj->id, $obj->title ) ;
   		} 
   		
   		$this->_lists['types'] = JHTML::_('select.genericlist',	$types, 'type_id', 'class="inputbox" size="1" ', 'value', 'text', (int) $this->_question->type_id ) ;
   				
		return $this->_lists ;
	}	
	
	/**
	 * Get the category of the question
	 * 
	 * @param $questionId
	 * @return array 
	 */
	function getCategoryOfQuestion($questionId) 
	{
		$query = 'SELECT categories.*' .
		' FROM #__jquarks_questions AS questions' .
		' LEFT JOIN #__jquarks_categories AS categories ON categories.id = questions.category_id' .
		' WHERE questions.id = ' . $questionId ;		
		
		$this->_db->setQuery($query) ;
		return $this->_db->loadAssoc() ;
	}
	
	/**
	 * Check if the question is assigned to the set
	 * 
	 * @param $set
	 * @param $question
	 * @return int id of the assignation
	 */
	function questionAssignedToSet($questionId, $setId) 
	{
		$query = 'SELECT soqh.id' .
		' FROM #__jquarks_setsofquestions_questions AS soqh' .
		' WHERE soqh.setofquestions_id = ' . $setId . 
		' AND soqh.question_id = ' . $questionId ;
		
		$this->_db->setQuery($query) ;
		return $this->_db->loadResult() ; 
	}
	
	/**
	 * Storing the question and it's propositions
	 * 
	 * @return boolean
	 */
	function store() 
	{
		$this->_row =& $this->getTable();
		$this->_propositionsTable =& JTable::getInstance('proposition', 'Table');
		
		$data = JRequest::get( 'post', JREQUEST_ALLOWRAW ) ;
                $data['statement'] = JRequest::getVar( 'statement', '', 'post', 'string', JREQUEST_ALLOWHTML );
                if ($data['description'] === '')
                {
                    $data['description'] = substr(strip_tags($data['statement']), 0, 100) ;
                }

		if ( ! (int)$data['category_id']) {
			$data['category_id'] = NULL ;
		}
		
		$this->_id =  $data['id'] ;
		$correctCount = 0 ;
			
		if ( ! $this->_row->save($data)) { // saving the question
			return false ; 
		}
		
		$new = 0;
		if ( $data['id'] == 0 ) // case of a new question get the id of the inserted row 
		{ 
			$data['id'] = $this->_id = $this->_db->insertid() ;
			$new = 1;
		}
	    
                $propNumber =  1 ;
		
		while ( array_key_exists('prop'.$propNumber, $data ) ) // while we have propositions 
		{
			if (array_key_exists('propid'.$propNumber, $data) && ! $new) { // existant proposition
				$propdata['id'] = $data['propid'.$propNumber];
			} else { // new proposition
				$propdata['id'] = 0 ;
			}
			
			$propdata['answer'] = $data['prop'.$propNumber] ;
			
			if (array_key_exists('propcorrect'.$propNumber, $data)) // the proposition is set to correct 
			{
				$propdata['correct'] = 1 ;
				$correctCount++ ;				
			} else { // the proposition is false
				$propdata['correct'] = 0 ;
			}
			
			$propdata['question_id'] = $data['id'] ;
			
			if(array_key_exists('propdelete'.$propNumber, $data) ) // the proposition is checked to be deleted
			{	
				if (array_key_exists('propid'.$propNumber, $data) && !$new) // case of an already existant proposition
				{	
					if (!$this->_propositionsTable->delete($propdata['id'])) 
					{
						$this->getErrors($this->_db->getErrorMsg()) ;
						return false ;					
					}
				}
				
				if ($propdata['correct']) {
						$correctCount-- ;
				}
			} 
			else // the proposition is not set to be deleted 
			{ 
				if (!$this->_propositionsTable->save($propdata)) {
					return false ;
				}
			}		
				
			$propNumber++ ; 
		}	

		switch ($correctCount) // according to the number of correct proposition define the type of question
		{
			case 0 : // input
				$data['type_id'] = '1' ;
				break ;
			case 1 : // radio
				$data['type_id'] = '2' ;
				break ;
			default: // checkbox
				$data['type_id'] = '3' ;
		}
		
		if (array_key_exists('asCheck', $data) && $data['type_id'] == 2) { // radio as checkbox
			$data['type_id'] = '4' ;
		}

		if (!$this->_row->save($data)) { // updating the type of the question
			return false ;
		}
		
		return true ;
	}	

}
