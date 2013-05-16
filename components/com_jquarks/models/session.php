<?php
/**
 * JQuarks Component Session Model
 *
 * @version	$Id: session.php 89 2010-12-29 13:52:10Z fnaccache $
 * @author	IP-Tech Labs <labs@iptech-offshore.com>
 * @copyright	2009-2010 IP-Tech
 * @package 	JQuarks-Front-Office
 * @subpackage	Controllers
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

    private $_id;
    private $_session;
    private $_results;
    private $_totalInput;
    private $_totalQuestions;
    private $_inputEvaluated;

    function __construct()
    {
        parent::__construct();
        $this->_id = JRequest::getInt('id');
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getTotalQuestions()
    {
        return $this->_totalQuestions;
    }

    public function getTotalInput()
    {
        return $this->_totalInput;
    }

    public function getInputEvaluated()
    {
        return $this->_inputEvaluated;
    }

    /**
     * Get results after evaluation of answers
     *
     * @return arrayObject of questions and propositions evaluated
     *
     */
    function getResults()
    {
        $query = 'SELECT pp.question_id, pp.type_id, pp.statement,' .
                '           pp.proposition_id, pp.answer, pp.correct,' .
                '           aa.answer_id, aa.altanswer, aa.status, aa.score' .
                ' FROM ( SELECT DISTINCT q.id AS question_id, q.type_id, q.statement, p.id AS proposition_id, p.answer, p.correct' .
                '        FROM #__jquarks_quizzes_answersessions ans' .
                '           INNER JOIN #__jquarks_questions    q ON ans.question_id = q.id' .
                '           LEFT JOIN  #__jquarks_propositions p ON q.id = p.question_id' .
                '        WHERE ans.quizsession_id = ' . (int) $this->_id .
                '      ) pp' .
                ' LEFT JOIN  ( SELECT question_id, answer_id, altanswer, status, score' .
                '              FROM #__jquarks_quizzes_answersessions ans' .
                '              WHERE ans.quizsession_id = ' . (int) $this->_id .
                '            ) aa' .
                ' ON ( ( pp.proposition_id = aa.answer_id AND pp.question_id = aa.question_id )' .
                '      OR ( pp.type_id = 1 AND pp.question_id = aa.question_id AND aa.answer_id = 0 )' .
                '      OR ( pp.type_id > 2 AND pp.type_id < 5 AND pp.question_id = aa.question_id AND aa.answer_id = 0 )' .
                '    )';

        // fetch and get Results

        $this->_results = $this->_getList($query);

        // init vars
        $totalInput = 0;
        $totalQuestions = 0;
        $inputEvaluated = 0;

        $idq = null;
        foreach ($this->_results as $res)
        {
            if ($res->question_id != $idq)
            {
                $idq = $res->question_id;
                $totalQuestions++;
                if ($res->type_id == 1)
                {
                    $totalInput++;
                    if ($res->status != -1)
                    {
                        $inputEvaluated++;
                    }
                }
            }
        }

        $this->_totalInput = $totalInput;
        $this->_totalQuestions = $totalQuestions;
        $this->_inputEvaluated = $inputEvaluated;

        return $this->_results;
    }

    /**
     * get the quiz Id of this session
     *
     * @return int - quiz id
     */
    public function getQuizId()
    {
        $query = 'SELECT quiz_id' .
                ' FROM #__jquarks_quizsession qs, #__jquarks_users_quizzes uq' .
                ' WHERE qs.id = uq.id' .
                ' AND qs.affected_id = ' . (int) $this->_id;

        $this->_db->setQuery($query);
        $quizId = $this->_db->loadResult();

        return $quizId;
    }

    /**
     * Get the session of the current user's answer to the quiz
     *
     * @return array
     *
     * id (of the session), title (of the quiz), quiz_id, score, maxScore, unaswered, evaluate,
     * spent_time, started_on, finished_on, ip_address
     * user_id, givenname, familyname, email
     *
     */
    function getSession()
    {
        $query = 'SELECT quizSession.id AS id, quizzes.title, quizzes.id,' .
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
                ' WHERE quizSession.id = ' . $this->_id;

        $this->_db->setQuery($query);
        $this->_session = $this->_db->loadObject();

        if ($this->_db->getErrorNum())
        {
            return false;
        }

        return $this->_session;
    }
}
