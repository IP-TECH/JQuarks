<?php
/**
 * JQuarks Component Profiles Model
 * 
 * @version     $Id: profiles.php 59 2010-03-02 08:40:27Z fnaccache $
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


class JQuarksModelProfiles extends JModel 
{

    function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get the list of Profiles
     *  
     * @return array
     */
    public function getProfiles() 
    {
        if (empty( $this->_profiles )) 
        {
            $mainframe =& JFactory::getApplication();
            $context    = 'com_jquarks.profiles.list.' ;

            $this->_filter_order        = $mainframe->getUserStateFromRequest( $context.'filter_order', 'filter_order', 'title', 'cmd' );
            $this->_filter_order_Dir    = $mainframe->getUserStateFromRequest( $context.'filter_order_Dir', 'filter_order_Dir', '', 'word' );
                
            $limit      = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
            $limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int') ;
            
            $orderby = '' ;
            
            if($this->_filter_order_Dir) {
                $orderby = ' ORDER BY '.$this->_filter_order.' '.$this->_filter_order_Dir ;
            }
            
            $query = ' SELECT *' .
            ' FROM #__jquarks_profiles' . 
            $orderby ;
            
            $total = $this->_getListCount($query) ; 
            
            jimport('joomla.html.pagination') ;
            $this->_pageNav = new JPagination( $total, $limitstart, $limit ) ;
            
            $this->_db->setQuery($query) ;          
            $this->_profiles = $this->_getList( $query, $this->_pageNav->limitstart, $this->_pageNav->limit );
            if ($this->_db->getErrorNum()) {
                return false;
            }
        }
        
        return $this->_profiles ;     
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
     * Delete the selected profiles
     * 
     * @param $profilesIds array of int
     * @return boolean
     */
    function delete($profilesIds) 
    {
        $this->_profilesTable =& JTable::getInstance('profile', 'Table');
        
        foreach($profilesIds as $profileId) 
        {
            if (!$this->_profilesTable->delete( $profileId ))
            {
                $this->setError( $this->_db->getErrorMsg() ) ;
                return false ;
            }
        }
        return true ;
    }
    
}