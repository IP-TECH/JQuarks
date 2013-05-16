<?php

/**
 * JQuarks Component Quiz View
 * 
 * @version		$Id: view.html.php 81 2010-11-30 13:09:45Z fnaccache $
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

class JQuarksViewQuiz extends JView {

    function display($tpl = null) {
        
        $quiz = & $this->get('Quiz');
        $lists = & $this->get('lists');

        $this->assignRef('quiz', $quiz);
        $this->assignRef('lists', $lists);

        $isNew = ($quiz->id < 1);
        $text = $isNew ? JText::_('NEW') : JText::_('EDIT');

        $layout = $this->getLayout();

        if ($layout == 'default') {
            JToolBarHelper::title(JText::_('QUIZ') . ': <small><small>[ ' . $text . ' ]</small></small>');
        } else {
            JToolBarHelper::title(JText::_('QUIZ') . ': <small><small>[ ' . $quiz->title . ' ]</small></small>');
        }

        if ($layout == "assignUsers") {
            $assignedUsers = & $this->get('assignedUsers');
            $pageNavUsers = & $this->get('pageNavUsers');

            $this->assignRef('assignedUsers', $assignedUsers);
            $this->assignRef('pageNavUsers', $pageNavUsers);

            JToolBarHelper::custom('assignUsers', 'assign.png', 'assign.png', 'ASSIGN', true);
            JToolBarHelper::custom('unassignUsers', 'unpublish.png', 'unpublish.png', 'UNASSIGN', true);
            JToolBarHelper::custom('notifyUsers', 'send.png', 'send.png', 'NOTIFY', true);
        }

        if ($layout != "assignSets") {
            JToolBarHelper::save();
            JToolBarHelper::apply();
        }

        if ($layout == "assignSets") {
            $setsOfQuestions = & $this->get('setsOfQuestions');
            $pageNavSets = & $this->get('pageNavSets');

            $this->assignRef('setsOfQuestions', $setsOfQuestions);
            $this->assignRef('pageNavSets', $pageNavSets);
        }

        if ($isNew) {
            JToolBarHelper::cancel();
        } else {
            JToolBarHelper::cancel('cancel', 'Close');
        }

        JToolBarHelper::help('quiz', true);

        $config = & JFactory::getConfig();

        $db = &JFactory::getDBO();
        
        $formatDate = 'Y-m-d H:i:s';
        $neverCondition = null;
        if (!$quiz->publish_down=="Jamais" || !$quiz->publish_down=="Never" ) {
            $neverCondition = JHTML::_('date', $quiz->publish_down, '%Y');
        }
        
        $form = new JParameter('', JPATH_COMPONENT . DS . 'models' . DS . 'quiz.xml');
        
        $form->set('publish_up', JHTML::_('date', $quiz->publish_up, $formatDate, false));
        $form->set('paginate', $quiz->paginate['use_pagination']);
        $form->set('slide', $quiz->paginate['use_slide']);
        $form->set('questionPage', $quiz->paginate['question_page']);

        if ($neverCondition <= 1969 || $quiz->publish_down == $db->getNullDate()) {
            $form->set('publish_down', JText::_('NEVER'));
        } else {
            $form->set('publish_down', JHTML::_('date', $quiz->publish_down, $formatDate,  false));
        }

        $this->assignRef('form', $form);

        parent::display($tpl);
    }

}