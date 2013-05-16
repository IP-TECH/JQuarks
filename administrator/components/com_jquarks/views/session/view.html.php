<?php
/**
 * JQuarks Component Answer View
 * 
 * @version	$Id: view.html.php 90 2010-12-29 14:07:36Z fnaccache $
 * @author	IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Views
 * @link	http://www.iptechinside.com/labs/projects/show/jquarks
 * @since	0.1
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

jimport( 'joomla.application.component.view' );


class JQuarksViewSession extends JView 
{
    function display($tpl = null)
    {
        $candidateModel =& JModel::getInstance('candidate','JQuarksModel');

        $answers = $this->get('answers') ;
        $session = $this->get('session') ;
        $profiles = $candidateModel->getUserProfiles($session->user_id) ;

        // variables of extended view of answers details
        $sessionModel =& $this->getModel('session');
        $sessionAnswers           = $sessionModel->getSessionAnswers();
        $questionsAndPropositions = $sessionModel->getQuestionsAndPropositions();
        $formattedAnswers = $sessionModel->formatQuestionsAndAnswers($questionsAndPropositions, $sessionAnswers);
        $this->assignRef('formattedAnswers',  $formattedAnswers);
        //$questions = $this->get('questions');
        //$QuestionsModel =& JModel::getInstance('question','JQuarksModel');

//        foreach ($questions as $question)
//        {
//            $QuestionsModel->setId($question->id);
//            $question =& $QuestionsModel->getQuestion();
//            $propositions =& $QuestionsModel->getPropositions();
//            $quiz[] = array('question' => $question, 'propositions' => $propositions);
//        }

        $this->assignRef('answers',  $answers);
        $this->assignRef('session',  $session);
        $this->assignRef('profiles', $profiles);
        //$this->assignRef('quiz',     $quiz);

        $title = $session->title . ': <small><small>[ ' ;
        if (!$session->givenname && !$session->familyname) {
                $title .= JText::_('GUEST') .' ]</small></small>' ;
        } else {
                $title .= $session->givenname . " " . $session->familyname .' ]</small></small>' ;
        }

        JToolBarHelper::title( $title, 'generic.png' );
        JToolBarHelper::cancel( 'cancel', 'Close' ) ;

        parent::display($tpl);
    }
}
