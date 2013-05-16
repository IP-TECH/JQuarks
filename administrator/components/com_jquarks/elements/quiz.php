<?php
/**
 * JQuarks Component Back-office main Controller
 * 
 * @version     $Id: quiz.php 59 2010-03-02 08:40:27Z fnaccache $
 * @author      IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright   2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Controllers
 * @link        http://www.iptechinside.com/labs/projects/show/jquarks
 * @since       0.3.0
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



class JElementQuiz extends JElement
{
    /**
     * Element name
     *
     * @access  protected
     * @var     string
     */
    public $_name = '';

    function fetchElement($name, $value, &$node, $control_name)
    {
        $mainframe =& JFactory::getApplication();
        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jquarks'.DS.'tables');
                
        $db         =& JFactory::getDBO();
        $doc        =& JFactory::getDocument();
        $template   = $mainframe->getTemplate();
        $fieldName  = $control_name.'['.$name.']';
        
        $quiz =& JTable::getInstance('quiz', 'table');
        
        if ($value) {
            $quiz->load($value);
        } else {
            $quiz->title = JText::_('Select a Quiz');           
        }
        
        $js = "
        function jSelectQuiz(id, title, object) {
            document.getElementById(object + '_id').value = id;
            document.getElementById(object + '_name').value = title;
            document.getElementById('sbox-window').close();
        }";
        $doc->addScriptDeclaration($js);

        JHTML::_('behavior.modal', 'a.modal');

        $link = 'index.php?option=com_jquarks&amp;view=quizelement&amp;tmpl=component&amp;object='.$name;
                
        $html = "\n".'<div style="float: left;"><input style="background: #ffffff;" type="text" id="'.$name.'_name" value="'.htmlspecialchars($quiz->title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" /></div>';
        $html .= '<div class="button2-left"><div class="blank"><a class="modal" title="'.JText::_('Select a Quiz').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}">'.JText::_('Select').'</a></div></div>'."\n";
        $html .= "\n".'<input type="hidden" id="'.$name.'_id" name="'.$fieldName.'" value="'.(int)$value.'" />';
        
        return $html;
    }
}
?>