<?php
/**
 * JQuarks Component Profiles Controller
 * 
 * @version     $Id: profiles.php 59 2010-03-02 08:40:27Z fnaccache $
 * @author      IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright   2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Controllers
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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

class JQuarksControllerProfiles extends JQuarksController 
{
    function __construct() 
    {
        parent::__construct();
    }
    
    function edit() 
    {
        $cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;
        
        $link = 'index.php?option=com_jquarks&view=profile&cid[]=' . $cids[0] ;
        $this->setRedirect($link) ;
    }   
    
    function remove() 
    {
        JRequest::checkToken() or jexit( 'Invalid Token' );     
        
        $cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;
        
        $model = $this->getModel('profiles');
        
        if ($model->delete($cids)) 
        {
            $msg = JText::_( 'PROFILE(S)_DELETED' ) ;
            $type = "message" ;
        } 
        else
        {
            $msg = JText::_( 'ERROR_DELETING_PROFILE(S)' ) ;
            $type = "error" ;
        }
        
        $link = 'index.php?option=com_jquarks&view=profiles' ;
        $this->setRedirect($link, $msg, $type) ;
    }
    
    function save() 
    {
        JRequest::checkToken() or jexit( 'Invalid Token' );
        
        $model = $this->getModel('profile');
        
        if ($model->store()) 
        {
            $msg = JText::_( 'PROFILE_SAVED' ) ;
            $type = "message" ;
        } 
        else 
        {
            $msg = JText::_( 'ERROR_SAVING_PROFILE' ) ;
            $type = "error" ;           
        } 

        if ($this->getTask() == 'save') {
            $link = 'index.php?option=com_jquarks&view=profiles' ;  
        } else {
            $link = 'index.php?option=com_jquarks&view=profile&task=edit&cid[]=' . $model->getId() ;
        }

        $this->setRedirect($link, $msg, $type) ;
    }
        
    function cancel() 
    {      
    	JRequest::checkToken() or jexit( 'Invalid Token' );
    	 
        $this->setRedirect( 'index.php?option=com_jquarks&controller=profiles' ) ;
    }
}