<?php
/**
 * JQuarks Component Answer Model
 * 
 * @version	$Id: session.php 90 2010-12-29 14:07:36Z fnaccache $
 * @author	IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Models
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

defined('_JEXEC') or die();

jimport('joomla.application.component.model');


class JQuarksModelSession extends JModel 
{
	public $_id ;
	public $_answers ; 
	public $_session ;
	
	function __construct() 
	{
		parent::__construct();
		
		$array = JRequest::getVar('cid', 0, '', 'array') ;	 	
	 	$this->_id = (int)$array[0] ;
	}
	
	/**
	 * Get the answers of the user for the current session
	 * 
	 * @return array
	 */
	function getAnswers() 
	{
		$query = 'SELECT questions.type_id, questions.description, questions.statement AS question, propositions.answer AS answer, quizSessAns.id, quizSessAns.status, quizSessAns.score, quizSessAns.answer_id, quizSessAns.altanswer AS altanswer' .
		' FROM #__jquarks_quizzes_answersessions AS quizSessAns' .
		' LEFT JOIN #__jquarks_questions AS questions ON questions.id = quizSessAns.question_id' .
		' LEFT JOIN #__jquarks_propositions AS propositions ON propositions.id = quizSessAns.answer_id' . 
		' WHERE quizSessAns.quizSession_id = ' . $this->_id ;
		
		$this->_answers = $this->_getList($query);
		if ($this->_db->getErrorNum()){
			return false ;
		}
		
		return $this->_answers ;
	}

	/**
	 * Get the session of the current user's answer to the quiz
	 * 
	 * @return array
	 */
	function getSession() 
	{
		$query = 'SELECT quizSession.id AS id, quizzes.title,' .
		' ( SELECT sum(score)  
			  FROM #__jquarks_quizzes_answersessions
			  WHERE quizsession_id = quizSession.id
			  AND status <> -1 ) AS score,' .
                ' ( SELECT count(distinct(question_id))
			  FROM #__jquarks_quizzes_answersessions 
			  WHERE quizsession_id = quizSession.id ) AS maxScore,' .
		' ( SELECT count(id)
			  FROM #__jquarks_quizzes_answersessions 
			  WHERE status=-2
			  AND quizsession_id = quizSession.id ) AS unanswered,' .
		' ( SELECT count(id)
			  FROM #__jquarks_quizzes_answersessions 
			  WHERE status=-1
			  AND quizsession_id = quizSession.id ) AS evaluate,' .
		' quizSession.spent_time, quizSession.started_on, quizSession.finished_on, quizSession.ip_address,' .	  
		' sessionWho.user_id, sessionWho.givenname, sessionWho.familyname, sessionWho.email' .
		' FROM #__jquarks_quizsession AS quizSession' .
		' LEFT JOIN #__jquarks_users_quizzes AS users_quizzes ON users_quizzes.id = quizSession.affected_id' .
		' LEFT JOIN #__jquarks_quizzes AS quizzes ON quizzes.id = users_quizzes.quiz_id' .
		' LEFT JOIN #__jquarks_quizzes_answersessions AS quizSessAns ON quizSessAns.quizsession_id = quizSession.id' .
		' LEFT JOIN #__jquarks_sessionwho AS sessionWho ON sessionWho.session_id = quizSession.id' .
		' WHERE quizSession.id = ' . $this->_id ;
		
		$this->_db->setQuery($query) ;
		$this->_session = $this->_db->loadObject() ;
		if ($this->_db->getErrorNum()){
			return false ;
		}
		
		return $this->_session ;
	}

	/**
	 * Change the state of the answer of an input question
	 * 
	 * @param $correct 
	 * @return boolean
	 */
	function correct($correct) 
	{
		$this->_row =& JTable::getInstance('quizzes_answersessions', 'Table');
		
	 	$answer['id'] = JRequest::getVar('sessAnsId') ;
		$answer['status'] = $correct ;
	 	if ($correct == 1) { 
	 		$answer['score'] = 1 ;
	 	} else {
	 		$answer['score'] = 0 ;
	 	}
	 	
	 	return $this->_row->save($answer) ;
	}

        
        /**
         * Return the questions of the session quiz
         *
         * @return array
         */
        function getQuestions()
        {
            $query = ' SELECT questions_id'.
                     ' FROM #__jquarks_quizsession'.
                     ' WHERE id = '.$this->_id;

            $this->_db->setQuery($query);
            $questions = $this->_db->loadResult();

            $query = ' SELECT *'.
                     ' FROM #__jquarks_questions'.
                     ' WHERE id IN ('.$questions.')';
            
            return $this->_getList($query);
        }

        
        function getQuestionsAndPropositions()
        {
            $query = ' SELECT questions_id'.
                     ' FROM #__jquarks_quizsession'.
                     ' WHERE id = '.$this->_id;

            $this->_db->setQuery($query);
            $questions_ids = $this->_db->loadResult();

            $query = 'SELECT q.type_id AS type_id, q.id AS question_id, q.statement, q.description, p.id AS proposition_id, answer AS proposition, correct'.
            ' FROM #__jquarks_questions q'.
            ' LEFT JOIN #__jquarks_propositions p ON q.id = p.question_id'.
            ' WHERE q.id IN ('.$questions_ids.')';

            $this->_db->setQuery($query);
            $rows = $this->_db->loadObjectList();
            return $rows;
        }

        function getSessionAnswers()
        {
            $query = ' SELECT *'.
                     ' FROM #__jquarks_quizzes_answersessions'.
                     ' WHERE quizsession_id = '.$this->_id;

            $this->_db->setQuery($query);
            $rows = $this->_db->loadObjectList();
            return $rows;
        }

        function formatQuestionsAndAnswers($questions, $answers)
        {
            $format = array();
            
            //format questions and propositions
            foreach ($questions as $row)
            {
                $qid = $row->question_id;
                $pid = $row->proposition_id;
                $format[$qid]['type_id']            = $row->type_id;
                $format[$qid]['statement']          = $row->statement;
                $format[$qid]['propositions'][$pid] = array('id'          => $pid,
                                                            'proposition' => $row->proposition,
                                                            'correct'     => $row->correct);
            }

            // format answers
            foreach ($answers as $row)
            {
                $qid = $row->question_id;

                // si pas de r�ponse
                if ($row->answer_id == 0)
                {
                    // si question input
                    if ($format[$qid]['type_id'] == '1')
                    {
                        $format[$qid]['altanswer'] = $row->altanswer;
                        $format[$qid]['answer_session_id'] = $row->id;
                    }
                    $format[$qid]['status']    = $row->status;
                    $format[$qid]['score']     = $row->score;
                }
                else
                {
                    $format[$qid]['propositions'][$row->answer_id]['status'] = $row->status;
                    $format[$qid]['propositions'][$row->answer_id]['score']  = $row->score;
                }
                
            }
            return $format;
        }
}
