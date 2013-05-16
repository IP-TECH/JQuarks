<?php
/**
 * JQuarks Component Who Controller
 * 
 * @version		$Id: who.php 59 2010-03-02 08:40:27Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package 	JQuarks-Front-Office
 * @subpackage	Controllers
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


class JQuarksControllerWho extends JQuarksController 
{
	function __construct() 
	{
		parent::__construct();
	}
	
	function display() 
	{		
		JRequest::setVar('view','_who') ;
		JRequest::setVar('layout','_form') ;
		
		parent::display() ;
	}
	
	function store() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token - task store, who controller' );

        // store session who
		$modelWho =& $this->getModel('who') ;

        if ($modelWho->store()) {
			$msg = JText::_( 'RECORDED' ) ;
		} else {
			$msg = JText::_('ERROR_RECORDING') ;
		}

        // if show_results then redirect
		$id = $modelWho->_sessionId;
        $quizModel =& $this->getModel("quiz");
        $show = $quizModel->isShowResults($id);

        if ($show) {
            $link = JRoute::_('index.php?option=com_jquarks&view=session&id=' . $id . "&". JUtility::getToken() . "=1", false);
        } else {
            $link = JRoute::_('index.php?option=com_jquarks&view=quizzes') ;
        }

        $this->setRedirect($link, $msg) ;

	}
    
}