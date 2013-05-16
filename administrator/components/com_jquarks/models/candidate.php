<?php
/**
 * JQuarks Component Candidate Model
 * 
 * @version     $Id: candidate.php 59 2010-03-02 08:40:27Z fnaccache $
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


class JQuarksModelCandidate extends JModel 
{

    public function __construct()
    {
        parent::__construct();
        
        $array = JRequest::getVar('cid', 0, '', 'array') ;      
        $this->_id = (int)$array[0] ;
    } 
    
    public function getUser() 
    {
    	$query = 'SELECT *' .
    	' FROM #__users' .
    	' WHERE id = ' . $this->_id ;
    	
    	$this->_db->setQuery($query) ;    	
    	return $this->_db->loadObject() ;
    }
    
    /**
     * Get the list of profiles and their assignation status to the current user
     * 
     * @param $userId
     * @return object
     */
    public function getUserProfiles($userId = null) 
    {   
    	if ($userId) {
    		$this->_id = $userId;
    	}
    	
    	$query = 'SELECT profiles.title, profiles.id,' .
    	                                 '(SELECT id
    	                                  FROM #__jquarks_users_profiles AS users_profiles
    	                                  WHERE users_profiles.user_id = ' . $this->_id . '
    	                                  AND users_profiles.profile_id = profiles.id) AS assigned' .
    	' FROM #__jquarks_profiles AS profiles' ;
    	
    	return $this->_getList($query) ;
    }    
    
    /**
     * Get the list of quizzes and the user affectation state to them
     * 
     * @return array
     */
    public function getUserQuizzes()
    {
        $mainframe =& JFactory::getApplication();
        $context                    = 'com_jquarks.candidat.list.' ;
        $this->filter_assigned      = $mainframe->getUserStateFromRequest( $context.'filter_assigned', 'filter_assigned', '', 'string') ;
        $this->_filter_order        = $mainframe->getUserStateFromRequest( $context.'filter_order', 'filter_order', 'name', 'cmd' ) ;
        $this->_filter_order_Dir    = $mainframe->getUserStateFromRequest( $context.'filter_order_Dir', 'filter_order_Dir', '', 'word') ;
        $search                     = $mainframe->getUserStateFromRequest( $context.'search', 'search', '', 'string') ;
        $search                     = JString::strtolower($search) ;
        
        
        $limit      = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
        $limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int') ;
        
        $where = array();
        
        $orderBy = '' ;
        
        if ($this->_filter_order_Dir){
            $orderBy    = ' ORDER BY '.$this->_filter_order.' '. $this->_filter_order_Dir ;
        }
        
       /* if ($this->filter_assigned) 
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
        }*/
        
        if ($search) {
            $where[] = ' LOWER(quizzes.title) LIKE '.$this->_db->Quote( '%'.$this->_db->getEscaped( $search, true ).'%', false );
        }
        
        $where = count( $where ) ? ' WHERE ' . implode( ' AND ', $where) : '' ;
        
        $query = 'SELECT quizzes.id, quizzes.title, quizzes.published, quizzes.access_id AS private, 
                                    (SELECT id
                                     FROM #__jquarks_users_quizzes AS users_quizzes
                                     WHERE users_quizzes.user_id = ' . $this->_id . ' 
                                     AND users_quizzes.quiz_id = quizzes.id
                                    ) AS assigned,' .
                                    '(SELECT archived
                                     FROM #__jquarks_users_quizzes AS users_quizzes
                                     WHERE id = assigned 
                                    ) AS archived' .
        ' FROM #__jquarks_quizzes AS quizzes' 
        . $where
        . $orderBy ; 
        
//$this->_db->setQuery($query) ;
//echo $this->_db->getQuery() ; exit() ;        
        
        $total = $this->_getListCount($query) ; 
    
        jimport('joomla.html.pagination') ;
        $this->_pageNav = new JPagination($total, $limitstart, $limit) ;
        
        $this->_usersQuizzes = $this->_getList($query, $this->_pageNav->limitstart, $this->_pageNav->limit) ;
        if($this->_db->getErrorNum()) {
            return false ;          
        }
        
        return $this->_usersQuizzes ;
    }

    public function &getLists() 
    {
        $this->_lists['order'] = $this->_filter_order ;
        $this->_lists['order_Dir'] = $this->_filter_order_Dir ;
        
        return $this->_lists ;
    }
    
    public function getPageNav() 
    {
        return $this->_pageNav ;
    }
    
    public function getId()
    {
    	return $this->_id ;
    }
    
    public function store()
    {
    	$this->_users_profilesTable =& JTable::getInstance('users_profiles', 'Table');
    	
        $profiles = JRequest::getVar('profiles', 0, '', 'array') ;
        
        $query = 'SELECT id, profile_id' .
        ' FROM #__jquarks_users_profiles' .
        ' WHERE user_id = ' . $this->_id ;
        
        $this->_db->setQuery($query) ;
//echo $this->_db->getQuery() ;        
        $oldProfiles = $this->_db->loadAssocList('profile_id') ;
        
        foreach ($profiles AS $profile => $value) // assigning new profiles
        {
        	if ( $profile && !array_key_exists($profile, $oldProfiles) )
        	{
        		$row['id'] = 0 ;
        		$row['user_id'] = $this->_id ;
        		$row['profile_id'] = $profile ;

        		if (!$this->_users_profilesTable->bind($row)) {
        			return false ;
        		}
        		
        	    if (!$this->_users_profilesTable->store()) {
                    return false ;
                }
        	}
        }

        foreach ($oldProfiles AS $oldProfile) // deleting removed profiles
        {
        	if ( $oldProfile && !array_key_exists($oldProfile['profile_id'], $profiles))
        	{
        	    if ( !$this->_users_profilesTable->delete($oldProfile['id']) ) {
                    return false ;
                }
        	}
        }
        
        return true ;
    }   
    
}