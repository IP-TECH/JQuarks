
<?php
/**
 * JQuarks Component Quiz Model
 * 
 * @version		$Id: quiz.php 82 2010-11-30 13:42:10Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package 	JQuarks-Front-Office
 * @subpackage	Models
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

jimport('joomla.application.component.model');


class JQuarksModelQuiz extends JModel 
{
	var $_questions = null ;
	var $_propositions = null ;
	var $_sessionId = null ;
	
	function __construct() 
	{
		parent::__construct();
				
		$this->_id = (int) JRequest::getVar('id') ; 
		
		// getting the current user
		$this->_user =& JFactory::getUser();		
		
		if ($this->_user->guest) {
			$this->_userId = -1 ;
		} else {
			$this->_userId = $this->_user->id ;
		}

		$this->quizSessionTable =& JTable::getInstance('quizsession', 'Table') ;
		$this->sessionWhoTable =& JTable::getInstance('sessionwho', 'Table') ;
		$this->quizAnswerTable =& JTable::getInstance('quizzes_answersessions', 'Table') ;
		$this->users_quizzesTable =& JTable::getInstance('users_quizzes', 'Table') ;
	}


    /**
     * sets the id of the quiz
     *
     */
    function setId($id = 0)
    {
        $this->_id = $id;
    }


	
	/**
	 * Get the affectation id of the current user to the quiz
	 * 
	 * @param $quizId
	 * @return int
	 */
	function getAffectedId($quizId) 
	{
		$config		=& JFactory::getConfig();
		$jnow		=& JFactory::getDate();
		$jnow->setOffset( $config->getValue('config.offset' ));
		$now		= $jnow->toMySQL(true);
		
		$query = 'SELECT users_quizzes.id' .
		' FROM #__jquarks_users_quizzes AS users_quizzes' .
		' JOIN #__jquarks_quizzes as qui ON qui.id = users_quizzes.quiz_id ' .
		' WHERE users_quizzes.user_id = ' . $this->_userId .
		' AND users_quizzes.quiz_id = ' . (int)$quizId .
		' AND users_quizzes.archived <> 1' .
		' AND qui.published = 1' .
		' AND qui.publish_up < "' . $now . '"' .
		' AND ( qui.publish_down = "0000-00-00 00:00:00" OR qui.publish_down > "' . $now . '" )' ;
		
		$this->_db->setQuery($query) ;
		$affectedId = $this->_db->loadResult() ;

		if($this->_db->getErrorNum()) {
			return false ;			
		}

		return $affectedId ;
	}
	
        /**
         * True if the quiz of the session argumenthas the property show_results set to 1,
         * else return false
         * 
         * @param int $session_id
         * @return boolean
         * 
         */
        function isShowResults($session_id)
        {
            $query = 'SELECT quizzes.show_results'.
            ' FROM #__jquarks_quizsession AS session, #__jquarks_users_quizzes AS users, #__jquarks_quizzes AS quizzes' .
            ' WHERE session.id = ' . (int)$session_id .
            ' AND session.affected_id = users.id AND users.quiz_id = quizzes.id';
            $this->_db->setQuery($query) ;
            return $this->_db->loadResult() ;
           

        }


	/**
	 * Get the selected Quiz
	 * 
	 * @param $quizId
	 * @return array
	 */
	function &getQuiz($quizId = null) 
	{
		if ($quizId) {
			$this->_id = (int)$quizId ;
		}
		
		$config 	=& JFactory::getConfig();
		$jnow		=& JFactory::getDate();
		$jnow->setOffset( $config->getValue('config.offset' ));
		$now		= $jnow->toMySQL(true);
		
		$query = 'SELECT *' .
		' FROM #__jquarks_quizzes AS qui' .		
		' WHERE qui.id = ' . $this->_id .
		' AND qui.published = 1' .
		' AND qui.publish_up < "' . $now . '"' .
		' AND (qui.publish_down = "0000-00-00 00:00:00"' .
                ' OR qui.publish_down > "' . $now . '")' ;
		
		$this->_db->setQuery($query) ;
		$this->_quiz = $this->_db->loadObject();
		if($this->_db->getErrorNum()) {
			return false ;
		}
		
		// creating the key 
		$paginate = array('use_pagination', 'use_slide', 'question_page') ;
		
		// making paginate an associative array containg the key created and the explode value it contained
		$toCombine = explode(' ', $this->_quiz->paginate) ;
		$this->_quiz->paginate = array_combine($paginate, $toCombine) ;

		// slicing the attribute we only keep the value
		foreach ($this->_quiz->paginate as &$attrib) 
		{
			list($att, $value) = explode('=', $attrib) ;
			$attrib = $value ;
		}
		unset($attrib) ;
	
		return $this->_quiz ;
	}
	
	/**
	 * Get the pagination configuration
	 * 
	 * @return array
	 */
	function getParams() 
	{
		$params =& JComponentHelper::getParams('com_jquarks');
		
		$this->_params['paginate'] = $params->get('usePagination') ;
		$this->_params['slide'] = $params->get('useSlide') ;
		$this->_params['question_page'] = $params->get('questionPage') ;
		
		return $this->_params ;
	}
	
	/**
	 * Get the questions from the custom sets of questions
	 * 
	 * @return array
	 */
	private function getCustomQuestions() 
	{
		$query = 'SELECT distinct(q.statement), q.id, q.type_id' .
		' FROM #__jquarks_questions AS q' .
		' LEFT JOIN #__jquarks_setsofquestions_questions AS soqHaveQ ON soqHaveQ.question_id = q.id' .
		' WHERE soqHaveQ.setofquestions_id IN' .
									  ' (SELECT quiHaveSoq.setofquestions_id 
		 								FROM #__jquarks_quizzes_setsofquestions AS quiHaveSoq
		 								WHERE quiHaveSoq.quiz_id = ' . $this->_id . ')' .
		' ORDER BY soqHaveQ.id' ;
		
		$questions = $this->_getList($query);
		if($this->_db->getErrorNum()) {
			return false ;
		}
		
		return $questions ;
	}
	
	/**
	 * Get the questions from the random sets of questions
	 * 
	 * @return array
	 */
	private function getRandomSets() 
	{
		$query = 'SELECT randomSet.*' . /*, /*(SELECT count(*) 
									   FROM #__jquarks_questions AS q
									   WHERE q.category_id = randomSet.category_id
									   AND q.archived = 0
									  ) AS totalQuestions' .*/
		' FROM #__jquarks_setsofquestions AS soq' .
		' JOIN #__jquarks_randomsets AS randomSet ON randomSet.set_id = soq.id' .
		' LEFT JOIN #__jquarks_quizzes_setsofquestions AS quiHaveSoq ON quiHaveSoq.setofquestions_id = soq.id' .
		' WHERE quiHaveSoq.quiz_id = ' . $this->_id .
		//' AND randomSet.category_id <> 0' .
		' AND randomSet.nquestions <> 0' ;
		
		$questions = $this->_getList($query) ;
		if($this->_db->getErrorNum()) {
			return false ;
		}
		
		return $questions ;
	}
	
	/**
	 * Get the questions assigned to the current session
	 * 
	 * @return array
	 */
	private function getSessionQuestions() 
	{
		$query = 'SELECT questions_id' .
		' FROM #__jquarks_quizsession' .
		' WHERE id = ' . $this->_sessionId ; 
		
		$this->_db->setQuery($query) ;
		$questions = $this->_db->loadResult() ; 
		if($this->_db->getErrorNum()) {
			return false ;
		}
		
		return $questions ;  
	}
	
	
	/**
	 * Get all the questions of a specific category that are affected to a given quiz
	 * 
	 * @todo this method is duplicated in model quiz on back-office remove this one and include the other model
	 * @param $categoryId
	 * @param $quizId
	 * @return array associative
	 */
	function getQuestionsOfCategoryAffectedToQuiz($quizId, $categoryId = NULL) 
	{
		$query = 'SELECT questions.*' .
		' FROM #__jquarks_quizzes_setsofquestions AS quiHaveSoq' .
		' JOIN #__jquarks_setsofquestions AS setsOfQuestions ON setsOfQuestions.id = quiHaveSoq.setofquestions_id' .
		' JOIN #__jquarks_setsofquestions_questions AS soqHaveQ ON soqHaveQ.setofquestions_id = setsOfQuestions.id' .
		' JOIN #__jquarks_questions AS questions ON questions.id = soqHaveQ.question_id' .
		' WHERE quiHaveSoq.quiz_id = ' . (int)$quizId ;
		 
		if ($categoryId) {
			$query .= ' AND questions.category_id = ' . $categoryId ;
		} else {
			$query .= ' AND questions.category_id IS NULL' ;
		}
		
		$this->_db->setQuery($query) ;
		return $this->_db->loadAssocList() ;
	}
	
	/**
	 * Return all the questions of the quiz and initiate the storing of the session
	 * 
	 * @return array
	 */
	function getQuestions() 
	{
		$questions = $this->getCustomQuestions() ;
		$randomSets = $this->getRandomSets($questions) ;
		
		$customQuestions = null;
		
	    // building the list of the custom questions
		if ($questions) 
		{
			foreach ($questions as $question) {
				$custom[] = $question->id ;	
			}
			$customQuestions = implode(",", $custom);
		}
		
		if ($randomSets) 
		{
			foreach ($randomSets as $randomSet) 
			{
				$neededNumber = $randomSet->nquestions ;
				
				if ($randomSet->category_id) {
					$totalNumber = $this->getQuestionsOfCategoryAffectedToQuiz($this->_quiz->id, $randomSet->category_id) ;
				} else {
					$totalNumber = $this->getQuestionsOfCategoryAffectedToQuiz($this->_quiz->id) ;
				}
								
				if($neededNumber > $totalNumber)
					$neededNumber = $totalNumber ;
				
				$query = 'SELECT *' .
				' FROM #__jquarks_questions AS questions' .
				' WHERE questions.archived = 0' ;
	
				if ($randomSet->category_id) {
					$query .= ' AND questions.category_id = ' . $randomSet->category_id ;
				} else {
					$query .= ' AND questions.category_id IS NULL';	
				}
				
				if($questions && $customQuestions) {
					$query .= ' AND questions.id NOT IN (' . $customQuestions . ')' ;
				}	
				
				$randomQuestions = $this->_getList($query) ;
				if($this->_db->getErrorNum()) {
					return false ;
				}
			
				if ( $neededNumber > 1) 
				{
					$randomQuestionsKeys = array_rand($randomQuestions, $neededNumber) ;

					foreach ($randomQuestionsKeys as $key) {
						$questions[] = $randomQuestions[$key] ;
					}
				} else {
					$questions[] = $randomQuestions[array_rand($randomQuestions,1)];
				}
			}
		}

		$this->_questions = $questions ;
		
		// Storing the user session
		if (!$this->storeSession()) {
			return false ;
		}
		
		return $this->_questions ;
	}
	
	/**
	 * Get the propositions of the retrieved questions
	 * 
	 * @param $questions
	 * @return array
	 */
	function getPropositions($questions = null) 
	{
		if($questions) 
		{
			foreach ($questions AS $key => $value) 
			{
				$query = 'SELECT prop.answer, prop.id, prop.correct' .
				' FROM #__jquarks_propositions AS prop' .
				' LEFT JOIN #__jquarks_questions AS q ON q.id = prop.question_id' .
				' WHERE prop.question_id = ' . $value ;
				
				$this->_propositions[$value] = $this->_getList($query) ; 
				if ($this->_db->getErrorNum()) {
					return false ;
				}
			}
		} 
		else 
		{
			foreach($this->_questions as $question) 
			{
				$query = 'SELECT prop.answer, prop.id, prop.correct' .
				' FROM #__jquarks_propositions AS prop' .
				' LEFT JOIN #__jquarks_questions AS q ON q.id = prop.question_id' .
				' WHERE prop.question_id = ' . $question->id ;
			
				$this->_propositions[$question->id] = $this->_getList($query) ; 
				if ($this->_db->getErrorNum()) {
					return false ;
				}
			}
		}
		
		return $this->_propositions ;
	}

	/**
	 * Return the selected session
	 * 
	 * @return unknown_type
	 */
	private function getSession() 
	{
		$query = 'SELECT *' .
		' FROM #__jquarks_quizsession' .
		' WHERE id = ' . $this->_sessionId ;
		
		$this->_db->setQuery($query) ;
		$session = $this->_db->loadObject() ;
		if ($this->_db->getErrorNum()) {
			return false ;
		}
		
		return $session ;
	}
	
	/**
	 * Store user information
	 * 
	 * @return boolean
	 */
	private function storeUserInfo($sessionId) 
	{
		$userInfo['session_id'] = $sessionId ;
		$userInfo['user_id'] = $this->_user->id ;
		
		$userInfo['email'] = $this->_user->email ;
		
		$name = explode(" ", $this->_user->name);
		
		$userInfo['givenname'] = $name[0] ;
		
		if ( array_key_exists(1, $name) ) {
			$userInfo['familyname'] = $name[1] ;
		} else {
			$userInfo['familyname'] = "" ;
		}
		
		return $this->sessionWhoTable->save($userInfo) ;
	} 
	
	/**
	 * If a user has already taken a quiz, when he is unassigned from it his assignation is archived
	 *
	 * @todo dumplicated in back office. find a way to clean it
	 * @param $assignationId
	 * @return boolean
	 */
	function archiveAssignation($assignationId) 
	{
		$query = 'UPDATE #__jquarks_users_quizzes AS users_quizzes' .
		' SET archived = 1' .
		' WHERE users_quizzes.id = ' . $assignationId ;
							
		$this->_db->execute($query) ;		
		if ($this->_db->getErrorNum()) {
			return false;
		}

		return true ;
	}
	
	/**
	 * UnassignUser unassign a specific user if the user have already took the quiz the assignation will be archived
	 * 
	 * @todo this is a modified version of the function present in quiz model back office find a way to merge them and get rid of this one
	 * @return boolean
	 */
	function unassignUser($userId, $quizId) 
	{
		$this->_users_quizzesTable =& JTable::getInstance('users_quizzes', 'Table') ;
		
		$line['quiz_id'] = (int)$quizId ;
		$line['user_id'] = $userId ;

		// getting the id of the record to delete / archive
		$query = 'SELECT users_quizzes.id' .
		' FROM #__jquarks_users_quizzes AS users_quizzes' . 
		' WHERE users_quizzes.quiz_id = ' . $line['quiz_id'] .
		' AND users_quizzes.user_id = ' . $line['user_id'] ;
		
		$this->_db->setQuery($query) ;
				
		$assignationId = $this->_db->loadResult() ;
		if ($this->_db->getErrorNum()) {
			return false ;
		}
		
		return $this->archiveAssignation($assignationId) ;			
	}
	
	/**
	 * Store the session's quiz
	 * 
	 * @return boolean
	 */
	private function storeSession() 
	{
		$lineSession['id'] = 0 ;
		
		// getting the id of affectation
		$quizId = JRequest::getVar('id') ;
		$lineSession['affected_id'] = $this->getAffectedId($quizId) ;

		// If the quiz is of unique session unaffecting the user from the quiz
		if ((int)$this->_quiz->unique_session) {
			$this->unassignUser($this->_userId, $this->_quiz->id) ;
		}
		
		// getting local time offset from config. Start time when the quiz was generated to the user
		$config =& JFactory::getConfig();
		$jnow		=& JFactory::getDate();
		$jnow->setOffset( $config->getValue('config.offset' ));
		$now		= $jnow->toMySQL(true);
		
		$lineSession['started_on'] = $now ; 
		
		// storing the ip address of the user
		$lineSession['ip_address'] = $_SERVER['REMOTE_ADDR'];
		
		// storing the questions
		$questions_id = array() ;
		foreach($this->_questions AS $question) {
			$questions_id[] = $question->id ;
		}
		$lineSession['questions_id'] = implode(",", $questions_id); 
		
		if(!$this->quizSessionTable->save($lineSession)) {
			return false ;
		}
		
		// getting the id of the session
		$query = 'SELECT id' .
		' FROM #__jquarks_quizsession AS quizSession' .
		' WHERE quizSession.affected_id = ' . $lineSession['affected_id'] .
		' AND quizSession.started_on = \''. $lineSession['started_on'] . '\'' ;
		
		$this->_db->setQuery($query) ;
		$this->_sessionId = $this->_db->loadResult() ;
		if ($this->_db->getErrorNum()) 
		{
			echo $this->_db->stderr();
			return false ;
		}
		
		// storing user's information
		if (!$this->storeUserInfo($this->_sessionId)) {
			return false ;
		}
		
		return true ;
	}
	
	function getSessionId() 
	{
		return $this->_sessionId ;
	}

	/**
	 * Get the number of seconds and return the corresponding hh:mm:ss time
	 * 
	 * @param $time seconds
	 * @return string
	 */
	function secondsToTime($time) 
	{
		$hours = (int)($time / 3600);
		$time = $time % 3600;
		$mins = (int)($time / 60);
		$seconds = $time % 60 ;

		return sprintf('%02d:%02d:%02d', $hours, $mins, $seconds);
	}


    /**
     * Storing the answers provided by the user
     *
     * answer status
     * -2 not anwsered
     * -1 not reviewed (case of input type)
     *  0 incorrect
     *  1 correct
     *  2 omitted
     * 
     * @return boolean
     * 
     */
    function storeAnswers()
    {
        $answers = JRequest::getVar('answers', 0, '', 'array');
        $this->_sessionId = JRequest::getVar('sessionId');

        $questions = $this->getSessionQuestions();

        // if the session is unique we unassign the user
        /* $this->getQuiz($this->_id);
          if ((int)$this->_quiz->unique_session) {
          $this->unassignUser($this->_user->id, $this->_id) ;
          } */

        // building an array with the questions ids
        $questions = explode(",", $questions);

        // getting the propositions for those questions
        $this->_propositions = $this->getPropositions($questions);

        foreach ($this->_propositions as $question_id => $propositions)
        {
            // emptying the arrays for the new iteration
            $answerAll = array();
            $answerLine = array();

            $answerLine['id'] = 0;
            $answerLine['quizsession_id'] = $this->_sessionId;
            $answerLine['question_id'] = $question_id;
            $answerLine['altanswer'] = '';
            $answerLine['answer_id'] = '';
            $answerLine['score'] = 0.0;

            if (array_key_exists($question_id, $answers))
            {
                // the user answered this question
                // $answer contain either the value of the answer or an array if the question is a multiple answers one
                $answer = $answers[$question_id];

                if (!$propositions) // case of an input question we have no proposition for this question
                {
                    $answerLine['altanswer'] = $answer;

                    if ($answer == '')
                    {
                        $answerLine['status'] = 0;
                        $answerLine['score']  = 0;
                    }
                    else
                    {
                        $answerLine['status'] = -1;
                        $answerLine['score']  = -1;
                    }

                    $answerAll[] = $answerLine;
                }
                elseif (is_array($answer)) // case of a checkbox multiple answers
                {
                    $checkBoxes[] = array();

                    $i = $correctNumber = 0;
                    $score = 0.0;
                    foreach ($propositions as $proposition)
                    {
                        $checkBoxes[$i]["id"] = $proposition->id;
                        $checkBoxes[$i]["correct"] = $proposition->correct ? true : false;
                        $checkBoxes[$i]["checked"] = array_key_exists($proposition->id, $answer) ? true : false;

                        if ($checkBoxes[$i]["correct"] && $checkBoxes[$i]["checked"])
                        {
                            $score++;
                        }

                        if (!$checkBoxes[$i]["correct"] && $checkBoxes[$i]["checked"])
                        {
                            $score--;
                        }

                        if ($checkBoxes[$i]["correct"])
                        {
                            $correctNumber++;
                        }

                        $i++;
                    }

                    $answerLine['score'] = (float) $score / $correctNumber;
                    if ($answerLine['score'] < 0)
                    { // we do not allow the score to be negeative
                        $answerLine['score'] = 0;
                    }

                    foreach ($checkBoxes AS $key => $value)
                    {
                        $answerLine['answer_id'] = $value['id'];

                        if ($value['checked'] && $value['correct'])
                        {
                            $answerLine['status'] = 1; //  answer is correct
                            $answerAll[] = $answerLine;
                            $answerLine['score'] = 0.0;
                        }

                        if ($value['checked'] && !$value['correct'])
                        {
                            $answerLine['status'] = 0; //  answer is incorrect
                            $answerAll[] = $answerLine;
                            $answerLine['score'] = 0.0;
                        }

                        if (!$value['checked'] && $value['correct'])
                        {
                            // 2 proposition is correct but user didn't check it (omitted)
                            $answerLine['status'] = 2;
                            $answerAll[] = $answerLine;
                            $answerLine['score'] = 0.0;
                        }
                    }
                }
                else // case of a unique radio answer
                {
                    foreach ($propositions as $proposition)
                    {
                        if ($proposition->id == $answer)
                        {
                            if ($proposition->correct)
                            {
                                $answerLine['score'] = $answerLine['status'] = 1;
                            }
                            else
                            {
                                $answerLine['score'] = $answerLine['status'] = 0;
                            }
                            break;
                        }
                    }
                    $answerLine['answer_id'] = $answer;

                    $answerAll[] = $answerLine;
                }
            }
            else // the question wasn't answered by the user
            {
                $answerLine['status'] = -2;
                $answerAll[] = $answerLine;
            }

            foreach ($answerAll as $answer)
            {
                if (!$this->quizAnswerTable->save($answer))
                {
                    return false;
                }
            }
        }

        // Updating the finishing time of the session
        $lineSession['id'] = $this->_sessionId;

        $session = $this->getSession();

        $config = & JFactory::getConfig();
        $jnow = & JFactory::getDate();
        $jnow->setOffset($config->getValue('config.offset'));
        $now = $jnow->toMySQL(true);
        $lineSession['finished_on'] = $now;

        $lineSession['spent_time'] = strtotime($lineSession['finished_on']) - strtotime($session->started_on);
        $lineSession['spent_time'] = $this->secondsToTime($lineSession['spent_time']);

        if (!$this->quizSessionTable->save($lineSession))
        {
            return false;
        }

        return true;
    }

}