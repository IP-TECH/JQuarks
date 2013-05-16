<?php
/**
 * JQuarks Component Category Controller
 * 
 * @version		$Id: category.php 59 2010-03-02 08:40:27Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Controllers
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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');


class JQuarksControllerCategory extends JQuarksController 
{
	public $_model ;
	
	function __construct() 
	{
		parent::__construct();

		$this->_model = $this->getModel('category') ;
		
		$this->registerTask( 'apply', 'save' );
	}
	
	function display() 
	{
		JRequest::setVar( 'hidemainmenu', 1 );
		JRequest::setVar('view','category') ;
		
		parent::display() ;
	}
			
	function save() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		if ($this->_model->store()) 
		{
			$msg = JText::_( 'CATEGORY_SAVED' ) ;
			$type = "message" ;
		} 
		else 
		{
			$msg = JText::_( 'ERROR_SAVING_CATEGORY' ) ;
			$type = "error" ;			
		} 

		if ($this->getTask() == 'save') {
			$link = 'index.php?option=com_jquarks&controller=categories' ;	
		} else {
			$link = 'index.php?option=com_jquarks&controller=category&task=edit&cid[]=' . $this->_model->getId() ;
		}

		$this->setRedirect($link, $msg, $type) ;
	}
		
	function cancel() 
	{		
		$this->setRedirect( 'index.php?option=com_jquarks&controller=categories' ) ;
	}

}