<?php
/**
 * JQuarks Component Candidates Model
 * 
 * @version     $Id: candidates.php 59 2010-03-02 08:40:27Z fnaccache $
 * @author      IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright   2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Models
 * @link        http://www.iptechinside.com/labs/projects/show/jquarks
 * @since       0.3
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


class JQuarksModelCandidates extends JModel 
{

    function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get all users profiles
     * 
     * @return array
     */
    private function getUsersProfiles()
    {
        $query = 'SELECT users.id, users.name, profiles.title'.
        ' FROM #__users AS users' .
        ' LEFT JOIN #__jquarks_users_profiles AS users_profiles ON users_profiles.user_id = users.id' .
        ' LEFT JOIN #__jquarks_profiles AS profiles ON profiles.id = users_profiles.profile_id' ;
        
        $this->_db->setQuery($query) ;
        return $this->_db->loadAssocList() ; 
    }
    
    /**
     * Get the list of Candidates
     *  
     * @return array
     */
    public function getCandidates() 
    {
        if (empty( $this->_profiles )) 
        {
            $mainframe =& JFactory::getApplication();
            $context = 'com_jquarks.candidates.list.' ;

            $this->_filter_order            = $mainframe->getUserStateFromRequest( $context.'filter_order', 'filter_order', 'title', 'cmd' );
            $this->_filter_order_Dir        = $mainframe->getUserStateFromRequest( $context.'filter_order_Dir', 'filter_order_Dir', '', 'word' );
            $this->filter_session_status    = $mainframe->getUserStateFromRequest( $context.'filter_session_status', 'filter_session_status', '', 'string') ;
            $search                         = $mainframe->getUserStateFromRequest( $context.'search', 'search', '', 'string') ;
            $search                         = JString::strtolower($search) ;
            
            $limit      = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
            $limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int') ;
            
            $orderby = '' ;
            $where[] = '' ;
            
	        if ($this->filter_session_status == 1) { // finished quiz only
	            $where[] = ' quizsession.finished_on <> \'0000-00-00 00:00:00\'' ;
	        }
	        
            if ($this->filter_session_status == 2) { // unfinished quiz only
                $where[] = ' quizsession.finished_on = \'0000-00-00 00:00:00\'' ;
            }
            
            if ($this->filter_session_status == 3) { // unstarted quiz only
                $where[] = ' (quizsession.finished_on IS NULL AND quizzes.title IS NOT NULL)' ;
            }
            
            if($this->_filter_order_Dir) {
                $orderby = ' ORDER BY '.$this->_filter_order.' '.$this->_filter_order_Dir ;
            } else {
            	$orderby = ' ORDER BY users.name' ;
            }
            
	        if ($search) {
	            $where[] = 'LOWER(users.name) LIKE '.$this->_db->Quote( '%'.$this->_db->getEscaped( $search, true ).'%', false );
	        }
            
	        $where = count( $where ) ? ' ' . implode( ' AND ', $where ) : '' ;
	        
            $query = ' SELECT users.id, users.name, quizzes.id AS quiz_id, quizzes.title AS quiz, quizzes.published AS published, quizsession.id AS session_id, quizsession.started_on, quizsession.finished_on' .
            ' FROM #__jquarks_users_quizzes AS users_quizzes' .
            ' RIGHT JOIN #__users AS users ON users.id = users_quizzes.user_id' .
            ' LEFT JOIN #__jquarks_quizzes AS quizzes ON quizzes.id = users_quizzes.quiz_id' .
            ' LEFT JOIN #__jquarks_quizsession AS quizsession ON quizsession.affected_id = users_quizzes.id' .  
            ' WHERE users.block = 0' . 
            $where .
            $orderby ;

            $result = $this->_getList( $query );

            $candidates = array() ;
            
            $user = $quizTitle = $finished_on = $profileTitle = null ;
            $nbrCandidates = -1 ;
            
            foreach ($result AS $row)
            {   
            	if ( $user != $row->name ) // a new user
            	{
            		$candidate = new stdClass() ; // building the user
            		$candidate->id = $row->id ;
            		$candidate->name = $user = $row->name ;
            		$candidate->quizzes = array() ;
            		$candidate->profiles = array() ;
            		
                    $quiz = new stdClass() ; // building the quiz
                    $quiz->id          = $row->quiz_id ;
                    $quiz->title       = $quizTitle = $row->quiz ;
                    $quiz->published   = $row->published ;
                    $quiz->session_id  = $row->session_id ;
                    $quiz->started_on  = $row->started_on ;
                    $quiz->finished_on = $finished_on = $row->finished_on ;           		 
           		
                    $candidate->quizzes[] = $quiz ; // adding the quiz to the user 
            		
            		$candidates[$candidate->id] = $candidate ; // adding the user to the list
            	}
            	else // existent user
            	{ 
            		if ($quizTitle != $row->quiz) // a new quiz
            		{ 
            			$quiz = new stdClass() ; // building the quiz
            			$quiz->id          = $row->quiz_id ;
            			$quiz->title       = $row->quiz ;
            			$quiz->published   = $row->published ;
            			$quiz->session_id  = $row->session_id ;
            			$quiz->started_on  = $row->started_on ;
            			$quiz->finished_on = $row->finished_on ;

            			$finished_on = $row->finished_on ;
            			$quizTitle = $row->quiz ;
            			
            			$candidates[$row->id]->quizzes[] = $quiz ; // adding the quiz to the list of the user's quiz
            		}
            		else
            		{ 
            			if ( strtotime($finished_on) < strtotime($row->finished_on) )
            			{
            				$nbrQuizzes = count($candidates[$row->id]->quizzes) -1 ;
            				
            				$candidates[$row->id]->quizzes[$nbrQuizzes]->id          = $row->id ;
            				$candidates[$row->id]->quizzes[$nbrQuizzes]->published   = $row->published ;
            				$candidates[$row->id]->quizzes[$nbrQuizzes]->session_id  = $row->session_id ;
            				$candidates[$row->id]->quizzes[$nbrQuizzes]->started_on  = $row->started_on ;
            				$candidates[$row->id]->quizzes[$nbrQuizzes]->finished_on = $row->finished_on ;
            				
            				$finished_on = $row->finished_on ;
            			}
            		}
            	}
            }

            // adding users's profiles
            $users_profiles = $this->getUsersProfiles() ; 
            foreach($users_profiles AS $user_profile) 
            {
            	if ($user_profile['title'] && array_key_exists($user_profile['id'], $candidates)) {
            	   $candidates[$user_profile['id']]->profiles[] = $user_profile['title'] ;
            	} 
            }
            
            $this->_candidates = $candidates ;
             
            if ($this->_db->getErrorNum()) {
                return false;
            }
            
            $total = count($this->_candidates) ; 
 
            jimport('joomla.html.pagination') ;
            $this->_pageNav = new JPagination( $total, $limitstart, $limit ) ;
        }
      
        return array_splice($this->_candidates, $this->_pageNav->limitstart, $this->_pageNav->limit ) ;     
    }
    
    function getPageNav() 
    {
        return $this->_pageNav ;
    }
    
    function getLists() 
    {
    	$status[] = JHTML::_('select.option', '0', '- '.JText::_('ALL_STATUS').' -');
        $status[] = JHTML::_('select.option', '1', JText::_('FINISHED')) ;
        $status[] = JHTML::_('select.option', '2', JText::_('UNFINISHED')) ;
        $status[] = JHTML::_('select.option', '3', JText::_('NOT_PASSED')) ;
        
        $this->_lists['status'] = JHTML::_('select.genericlist', $status, 'filter_session_status', 'class="inputbox" size="1" onchange="submitform();"', 'value', 'text', $this->filter_session_status) ;
        
    	
        $this->_lists['order'] = $this->_filter_order ;
        $this->_lists['order_Dir'] = $this->_filter_order_Dir ;
        
        return $this->_lists ;
    }
           
}