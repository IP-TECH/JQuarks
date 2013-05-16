<?php
/**
 * JQuarks Component Profile Model
 * 
 * @version     $Id: profile.php 59 2010-03-02 08:40:27Z fnaccache $
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


class JQuarksModelProfile extends JModel 
{
    function __construct() 
    {
        parent::__construct();      
        
        $array = JRequest::getVar('cid', 0, '', 'array') ;      
        $this->_id = (int)$array[0] ;
        
        $this->_profile = null;
    }
            
    /**
     * Get the selected profile or create a new one
     * 
     * @return object
     */
    public function &getProfile() 
    { 
        if (empty($this->_profile)) 
        {
            $query = 'SELECT *'.
                ' FROM #__jquarks_profiles'.
                ' WHERE id = '. $this->_id ;
            
            $this->_db->setQuery($query) ;
            $this->_profile = $this->_db->loadObject() ;
        }
        
        if (!$this->_profile) 
        {
            $this->_profile = new stdClass() ;
            $this->_profile->id = 0 ;
            $this->_profile->title = '' ; 
            $this->_profile->description = '' ;
        }
        
        return $this->_profile ;
    }
    
    public function getId() 
    {
        return $this->_id ;
    }
    
    /**
     * Create a new profile
     * 
     * @todo quite the same as store keep only one of the two
     * @return array
     */
    function addProfile() 
    {
        $this->_profilesTable =& JTable::getInstance('profile', 'Table') ;
        
        $data = JRequest::get('GET') ;
        
        $profile['id'] = 0 ;
        $profile['title'] = $data['title'] ;

        if (!$this->_profilesTable->save($profile)) {
            return false ;
        }
        
        $category['id'] = $this->_db->insertid();
        
        return $category ;
    }
    
    /**
     * Storing the profile
     *  
     * @param $profile array
     * @return boolean
     */
    public function store() 
    {
        $this->_row =& $this->getTable();
        
        $profile = JRequest::get( 'post' ) ;
        
        if(!$this->_row->save($profile)) {
            return false ;
        }
        
        if ($profile['id']) {
            $this->_id = $profile['id'] ;
        } else {
            $this->_id = $this->_db->insertid() ;   
        }
        
        return true ;
    }   
    
}