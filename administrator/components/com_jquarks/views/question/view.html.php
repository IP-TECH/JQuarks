<?php
/**
 * JQuarks Component Question View
 * 
 * @version		$Id: view.html.php 86 2010-12-01 16:28:01Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Views
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

jimport('joomla.application.component.view');


class JQuarksViewQuestion extends JView 
{
	function display($tpl = null)
	{
		//$editor =& JFactory::getEditor('tinymce');	
		$question =& $this->get( 'Question' );		
		$list =& $this->get( 'Lists' ) ;	
		$propositionsList =& $this->get( 'Propositions' ) ;

		if (JRequest::getVar('task') == 'copy') {
			$question->id = 0 ;
		}
		
		$this->assignRef('question', $question) ;
		$this->assignRef('lists', $list) ;
		$this->assignRef('propositions', $propositionsList) ;
		
		$isNew = ($question->id < 1);		
		$text = $isNew ? JText::_( 'NEW' ) : JText::_( 'EDIT' );
  		JToolBarHelper::title(   JText::_( 'QUESTION' ).': <small><small>[ ' . $text .' ]</small></small>' );		
		JToolBarHelper::save() ;
		JToolBarHelper::custom( 'saveContinue', 'save.png', 'save.png', 'SAVE_CONTINUE', false );
		JToolBarHelper::apply() ;
		
		if ($isNew) {
			JToolBarHelper::cancel();
		} else {
			JToolBarHelper::cancel( 'cancel', 'Close' ) ;
		}
		
		JToolBarHelper::help( 'question', true );

                // get the user preferred editor, otherwise from global configuration
                $user = JFactory::getUser();
                $editorName = $user->getParam('editor');
                if (is_null($editorName)) {
                    $editor =& JFactory::getEditor();
                } else {
                    $editor =& JFactory::getEditor($editorName);
                }

                switch ($editor->get("name"))
                {
                    case 'tinymce':
                        $params = array( 'smilies'=> '0' ,
                        'style'  => '1' ,
                        'layer'  => '0' ,
                        'table'  => '0' ,
                        'clear_entities'=>'0',
                        'relative_urls'=>'0',
                        'extended_elements' => "pre[name|class]",
                        );
                        break;

                    default:
                        $params = array();
                }
                $this->assignRef('editor', $editor) ;
                $this->assignRef('editor_params', $params) ;

		parent::display($tpl);		
	}
	
}