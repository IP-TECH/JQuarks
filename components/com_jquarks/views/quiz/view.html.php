<?php
/**
 * JQuarks Component Quiz View
 * 
 * @version		$Id: view.html.php 83 2010-11-30 14:44:15Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package 	JQuarks-Front-Office
 * @subpackage	Views
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

jimport( 'joomla.application.component.view');


class JQuarksViewQuiz extends JView 
{
	function display($tpl = null) 
	{
		$quiz     = $this->get('quiz') ;
		$params   = $this->get('params') ;
		
		$this->assignRef( 'quiz', $quiz);
		$this->assignRef('params', $params) ;
		
		if ($this->_layout == 'default')
		{
                    $questions       = $this->get('questions') ;
                    $propositions    = $this->get('propositions') ;
                    $sessionId       = $this->get('sessionId') ;
                    $isShowResults   = $this->getModel()->isShowResults($sessionId);

                    $this->assignRef( 'questions', $questions);
                    $this->assignRef( 'propositions', $propositions) ;
                    $this->assignRef( 'sessionId', $sessionId ) ;
                    $this->assignRef('isShowResults',$isShowResults);
		}		
		parent::display($tpl);
	}	
}
