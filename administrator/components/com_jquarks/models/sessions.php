<?php
/**
 * JQuarks Component Answers Model
 * 
 * @version		$Id: sessions.php 59 2010-03-02 08:40:27Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Models
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

class JQuarksModelSessions extends JModel 
{

	public $_filter_quiz ;
	public $_filter_order_Dir ;
	public $_filter_order ;
	public $_filter_user ;
	public $_filter_unfinished ;
	private $_filter_profile ;
	
	public $_sessions ;
	
	public $_lists ;
	public $_pageNav ;
	
	private $_profiles ;
	
	function __construct() 
	{
		parent::__construct();
		
		$this->_sessionsTable =& JTable::getInstance('quizsession', 'Table');
	}
	
	/**
	 * Get the sessions according to the user's filter
	 *  
	 * @return array 
	 */
	function &getSessions() 
	{
		$mainframe =& JFactory::getApplication();
		$context					= 'com_jquarks.sessions.list.' ;
		$this->_filter_order 		= $mainframe->getUserStateFromRequest( $context.'filter_order', 'filter_order', 'started_on', 'cmd' ) ;
		$this->_filter_order_Dir 	= $mainframe->getUserStateFromRequest( $context.'filter_order_Dir', 'filter_order_Dir', 'DESC', 'word') ;
		$this->_filter_quiz			= $mainframe->getUserStateFromRequest( $context.'filter_quiz', 'filter_quiz', 0,	'string' );		
		$this->_filter_user			= $mainframe->getUserStateFromRequest( $context.'filter_user', 'filter_user', 0,	'string' );				
		$this->_filter_unfinished   = $mainframe->getUserStateFromRequest( $context.'filter_unfinished', 'filter_unfinished', 0,	'string' );
		$this->_filter_profile		= $mainframe->getUserStateFromRequest( $context.'filter_profile', 'filter_profile', '', 'string') ;

		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int') ;
		
		$where = array();
	
		$orderby = '' ;
		
		if($this->_filter_order_Dir) {
			$orderby = ' ORDER BY ' . $this->_filter_order . ' ' . $this->_filter_order_Dir ;	
		}
				
		// getting the quiz id from the filter		
		if ($this->_filter_quiz) {
			$where[] = ' users_quizzes.quiz_id = ' . $this->_filter_quiz ;
		} 
		
		if ($this->_filter_user) {
			$where[] = ' users_quizzes.user_id = ' . $this->_filter_user ;
		}
		
		if ( $this->_filter_unfinished ) 
		{
			switch ($this->_filter_unfinished) 
			{
				case 1 : // show finished
					$where[] = ' quizSession.finished_on <> \'0000-00-00 00:00:00\'' ;
					break ;
				case 2 : // show unfinished	
					$where[] = ' quizSession.finished_on = \'0000-00-00 00:00:00\'' ;
					break ;
			}
		}
		
		if ( $this->_filter_profile ) {
			$where[] = ' profiles.id = ' . $this->_filter_profile ;
		} 
		
		$where	= count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' ;
		
		$query = 'SELECT distinct(quizSession.id) AS id, qui.title, qui.id AS name,' .
		' ( SELECT GROUP_CONCAT(profiles.title) 
              FROM #__jquarks_users_profiles AS users_profiles
              LEFT JOIN #__jquarks_profiles AS profiles ON users_profiles.profile_id = profiles.id
              WHERE users_profiles.user_id = sessionWho.user_id ) AS profile, ' .
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
		' sessionWho.givenname, sessionWho.familyname, sessionWho.email' .	  
		' FROM #__jquarks_quizsession AS quizSession' .
		' LEFT JOIN #__jquarks_users_quizzes AS users_quizzes ON users_quizzes.id = quizSession.affected_id' .
		' LEFT JOIN #__jquarks_quizzes AS qui ON users_quizzes.quiz_id = qui.id' .
		' LEFT JOIN #__jquarks_quizzes_answersessions AS quizSessAns ON quizSessAns.quizsession_id = quizSession.id' .
		' LEFT JOIN #__jquarks_sessionwho AS sessionWho ON sessionWho.session_id = quizSession.id' .
		' LEFT JOIN #__jquarks_users_profiles AS users_profiles ON users_profiles.user_id = sessionWho.user_id' .
		' LEFT JOIN #__jquarks_profiles AS profiles ON profiles.id = users_profiles.profile_id' . 
		$where . 
		' GROUP BY quizSession.id' .
		$orderby ;
	
 		$total = $this->_getListCount($query) ;	
		if($this->_db->getErrorNum()) {
			return false ;
		}
		
		// creating the pagination according to the number of questions
		jimport('joomla.html.pagination') ;
		$this->_pageNav = new JPagination( $total, $limitstart, $limit ) ;
		
		// getting the list of session
		if (empty( $this->_sessions )) 
		{
			$this->_sessions = $this->_getList( $query, $this->_pageNav->limitstart, $this->_pageNav->limit );
			if ($this->_db->getErrorNum()) {
				return false;
			}
		}
			
		return $this->_sessions ;
	}
		
	/*function getUsersProfiles()
	{
		$query = 'SELECT users_profiles.user_id, GROUP_CONCAT(profiles.title) FROM' .
		' #__jquarks_users_profiles AS users_profiles' . 
		' LEFT JOIN #__jquarks_profiles AS profiles ON users_profiles.profile_id = profiles.id' .
		' GROUP BY users_profiles.user_id' ;
		
		$this->_db->setQuery($query);
		echo $this->_db->getQuery(); exit();
	}*/
	
	/**
	 * Create the drop-down lists for the Quiz and the User
	 *  
	 * @return array
	 */
	function &getLists() 
	{
		$javascript		= ' onchange="document.adminForm.submit();" ';
		
		// quizzes filter
		$query = 'SELECT qui.*' .
		' FROM #__jquarks_quizzes AS qui' ;
		
		$quiz[] = JHTML::_('select.option', '0', '- '.JText::_('SELECT_QUIZ'). ' -');
		$this->_db->setQuery($query) ;
		$this->_quiz = $this->_db->loadObjectList() ;
		
		foreach( $this->_quiz as $obj ) {
			$quiz[] = JHTML::_('select.option', $obj->id, $obj->title ); 
		}
		
		$this->_lists['quiz'] = JHTML::_('select.genericlist',	$quiz, 'filter_quiz', 'class="inputbox" size="1" ' . $javascript , 'value', 'text', $this->_filter_quiz);
		
		// profiles filter
		$query = 'SELECT *' .
		' FROM #__jquarks_profiles' ;
		$this->_db->setQuery($query) ;
        $this->_profiles = $this->_db->loadObjectList() ;
        
		$profile[] = JHTML::_('select.option', '0', '- '.JText::_('SELECT_PROFILE'). ' -');
		foreach ($this->_profiles as $obj) {
		    $profile[] = JHTML::_('select.option', $obj->id, $obj->title );
		}
		
		$this->_lists['profiles'] = JHTML::_('select.genericlist', $profile, 'filter_profile', 'class="inputbox" size="1" ' . $javascript, 'value', 'text', $this->_filter_profile ) ;
		
		// users filter selected from the assigned users
		$user[] = JHTML::_('select.option', '0', '- '.JText::_('SELECT_USER').' -');

		$query = 'SELECT distinct(users_quizzes.user_id), u.givenname, u.familyname, users.username' .
		' FROM #__jquarks_users_quizzes as users_quizzes' .
		' LEFT JOIN #__jquarks_sessionwho AS u ON u.user_id = users_quizzes.user_id' .
		' LEFT JOIN #__users AS users ON users.id = users_quizzes.user_id' .
		' GROUP BY users_quizzes.user_id' ;
		
		$this->_db->setQuery($query);
		$objects = $this->_db->loadObjectList() ; 
		if($this->_db->getErrorNum()) {
			return false ;
	  	}
		
		foreach( $objects as $obj ) 
		{
			if($obj->user_id == -1) {
				$user[] = JHTML::_('select.option',  $obj->user_id, JText::_( 'GUEST' ) );
			} 
			else 
			{
				if (!$obj->givenname && !$obj->familyname ) { 
					$user[] = JHTML::_('select.option',  $obj->user_id, $obj->username );		
				} else {
					$user[] = JHTML::_('select.option',  $obj->user_id, $obj->givenname . " " . $obj->familyname );
				}
			}
		}
		
		$unfinished[] = JHTML::_('select.option', '0', JText::_('ALL_SESSIONS') );
		$unfinished[] = JHTML::_('select.option', '1', JText::_('FINISHED_SESSIONS') );
		$unfinished[] = JHTML::_('select.option', '2', JText::_('INTERRUPTED_SESSIONS') );
		
		$this->_lists['user'] = JHTML::_('select.genericlist',	$user, 'filter_user', 'class="inputbox" size="1" ' . $javascript , 'value', 'text', $this->_filter_user);
		$this->_lists['unfinished'] = JHTML::_('select.genericlist',	$unfinished, 'filter_unfinished', 'class="inputbox" size="1" ' . $javascript , 'value', 'text', $this->_filter_unfinished);
		$this->_lists['order'] = $this->_filter_order ;
		$this->_lists['order_Dir'] = $this->_filter_order_Dir ;
				
		return $this->_lists ;
	}
	
	/**
	 * Convert an object to an assosiative array
	 * 
	 * @TODO extract this in a helper
	 * @param $data as object
	 * @return array
	 */
	private function objectToArray($data) 
	{
	  	if(is_array($data) || is_object($data))
	  	{	  	
	    	$result = array(); 
		    foreach($data as $key => $value) { 
		      $result[$key] = $this->objectToArray($value); 
		    }
		    
		    return $result;
	  	}
	  
	  	return $data;
	}
	
	/**
	 * Remove accented caracters from the string
	 * 
	 * @TODO extract this in a helper
	 * @param $string
	 * @return string
	 */
	private function noAccented($string) 
	{
		$search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
		$replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
		
		return str_replace($search, $replace, $string);
	}
	
	/**
	 * Clear for the current quiz and current user the session that were not achieved
	 * the user only displayed the test without posting his answers
	 * 
	 * @return boolean
	 */
	function clearUnfinished() 
	{
		$mainframe =& JFactory::getApplication();
		$context					= 'com_jquarks.answers.list.' ;
		$this->_filter_quiz			= $mainframe->getUserStateFromRequest( $context.'filter_quiz', 'filter_quiz', 0,	'string' );		
		$this->_filter_user			= $mainframe->getUserStateFromRequest( $context.'filter_user', 'filter_user', 0,	'string' );				
		$this->_filter_unfinished   = $mainframe->getUserStateFromRequest( $context.'filter_unfinished', 'filter_unfinished', 0,	'string' );
		
		$where = array() ;
		
		if ($this->_filter_quiz) {
			$where[] = ' users_quizzes.quiz_id = ' . $this->_filter_quiz ;
		} 
		
		if ($this->_filter_user) {
			$where[] = ' users_quizzes.user_id = ' . $this->_filter_user ;
		}
		
		$where[] = ' quizSession.finished_on = \'0000-00-00 00:00:00\'' ;
		
		$where	= count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' ;
		
		$query = 'SELECT distinct(quizSession.id) AS id' .	  
		' FROM #__jquarks_quizsession AS quizSession' .
		' LEFT JOIN #__jquarks_users_quizzes AS users_quizzes ON users_quizzes.id = quizSession.affected_id' .
		' LEFT JOIN #__jquarks_quizzes AS qui ON users_quizzes.quiz_id = qui.id' .
		' LEFT JOIN #__jquarks_sessionwho AS sessionWho ON sessionWho.session_id = quizSession.id' .
		' LEFT JOIN #__jquarks_quizzes_answersessions AS quizSessAns ON quizSessAns.quizsession_id = quizSession.id' .
		$where ;
		
		$sessionsToClear = $this->_getList($query) ;

		foreach($sessionsToClear as $session) 
		{
			if (!$this->_sessionsTable->delete( $session->id ))
			{
				$this->setError( $this->_db->getErrorMsg() ) ;
				return false ;
			}
		}

		return true ;
	}
	
	/**
	 * Prompt the user to download a .CSV file of the list of the currenty displayed sessions
	 * 
	 * @TODO extract this to a helper ?
	 * @return boolean
	 */
	function exportCSV()
	{
		$sessions = $this->getSessions() ;
		
		foreach($sessions as &$session) 
		{
			$session = $this->objectToArray($session) ;
			 
			if ($session['givenname'] != "")
			{
				$session['name'] = $session['givenname'] . " " . $session['familyname'] . " " . $session['email'] ;
				array_pop($session) ; // givenname, familyname and email from the array
				array_pop($session) ;
				array_pop($session) ;
			}	
			else
				$session['name'] = JText::_('GUEST') ;
					
			if($session['finished_on'] == '0000-00-00 00:00:00')
				$session['finished_on']	 = JText::_('UNFINISHED') ;
				
			foreach ($session as $key => &$value) { 
				$value = $this->noAccented($value) ;
			}
			unset($value) ;
		}
		unset($session) ;
	
		// inserting the header
		$headerCSV = array(
			'id' => '#', 
			'title' => $this->noAccented(JText::_('QUIZ')),
		    'name' => $this->noAccented(JText::_('USER')),
		    'profile' => $this->noAccented(JText::_('PROFILE')),
		    'score' => $this->noAccented(JText::_('SCORE')),
		    'maxScore' => $this->noAccented(JText::_('MAX_SCORE')),
		    'unanswered' => $this->noAccented(JText::_('IS_UNANSWERED')),
		    'evaluate' => $this->noAccented(JText::_('TO_CORRECT')),
			'spent_time' =>  $this->noAccented(JText::_('SPENT_TIME')),
			'started_on' =>  $this->noAccented(JText::_('STARTED_ON')),
			'finished_on' =>  $this->noAccented(JText::_('FINISHED_ON')),
			'ip_address' => $this->noAccented(JText::_('IP_ADDRESS')),
		) ;
		array_unshift($sessions, $headerCSV) ;
		
		$csv = "" ;
		
		// writing to the file
		foreach($sessions as $session) 
		{
			$csv .= implode(';', $session) ;
			$csv .= "\n" ;
		}
		
		header('Content-Description: File Transfer');
		header('Content-Type: text/csv') ;
		header('Content-Disposition: attachment; filename=export.csv');	
		header('Content-Transfer-Encoding: binary');
    	header('Expires: 0');
    	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    	header('Pragma: public');
   
    	echo $csv ; exit() ;
	}
	
	function getPageNav() 
	{
		return $this->_pageNav ;
	}
	
	/**
	 * Delete the selected session answers
	 * 
	 * @param $cids array of session ids
	 * @return boolean
	 */
	function delete($sessionsIds) 
	{
		foreach($sessionsIds as $sessionId) 
		{
			if (!$this->_sessionsTable->delete( $sessionId ))
			{
				$this->setError( $this->_db->getErrorMsg() ) ;
				return false ;
			}
		}
		
		return true ;
	}
	
}