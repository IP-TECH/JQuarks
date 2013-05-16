<?php
/**
 * JQuarks Component Candidate Controller
 * 
 * @version     $Id: candidate.php 59 2010-03-02 08:40:27Z fnaccache $
 * @author      IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright   2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Controllers
 * @link        http://www.iptechinside.com/labs/projects/show/jquarks
 * @since       0.3
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

class JQuarksControllerCandidate extends JQuarksController 
{
    function __construct() 
    {
        parent::__construct();

        $this->_model = $this->getModel('candidate') ;
        
        $this->registerTask( 'apply', 'save' );
    }

    function display()
    {
        JRequest::setVar('view','candidate') ;
        
        parent::display() ;
    }
    
    function assignToQuiz()
    {
        $mainframe =& JFactory::getApplication();
    	$quizModel =& JModel::getInstance('quiz','JQuarksModel');
    	
        $data = JRequest::get('GET') ;
    	
    	$quiz = $quizModel->getQuiz($data['quizId']) ;
    	
    	if ($quizModel->assignUser($data['userId'], $data['quizId'])) 
        {
            $response['message'] = JText::_( 'THE_USER_HAVE_BEEN_ASSIGNED_TO_THIS_QUIZ') ;
            $response['type'] = "message" ;     
            $response['published'] = $quiz->published ? 1 : 0 ;    
        } 
        else 
        {
            $response['message'] = JText::_( 'ERROR_UNABLE_TO_ASSIGN_USER_TO_THE_QUIZ') ;
            $response['type'] = "error" ;
            $response['published'] = $quiz->published ? 1 : 0 ;
        }
        
        $response['task'] = 'assignToQuiz' ;
        
        $document =& JFactory::getDocument();
        $document->setMimeEncoding( 'application/json' );
        JResponse::setHeader( 'Content-Disposition', 'attachment; filename="assignToQuiz.json"' );
        
        echo json_encode($response) ;
        
        $mainframe->close() ;
    }
    
    function unassignFromQuiz()
    {
        $mainframe =& JFactory::getApplication();
        $quizModel =& JModel::getInstance('quiz','JQuarksModel');
        
        $data = JRequest::get('GET') ;
        
        $quiz = $quizModel->getQuiz($data['quizId']) ;
        
        if ($quizModel->unassignUser($data['userId'], $data['quizId'])) 
        {
            $response['message'] = JText::_( 'THE_USER_HAVE_BEEN_UNASSIGNED_FROM_THIS_QUIZ') ;
            $response['type'] = "message" ; 
            $response['published'] = $quiz->published ? 1 : 0 ;         
        } 
        else 
        {
            $response['message'] = JText::_( 'ERROR_UNABLE_TO_UNASSIGN_USER_FROM_THE_QUIZ') ;
            $response['type'] = "error" ;
            $response['published'] = $quiz->published ? 1 : 0 ;
        }
        
        $response['task'] = 'unassignFromQuiz' ;
        
        $document =& JFactory::getDocument();
        $document->setMimeEncoding( 'application/json' );
        JResponse::setHeader( 'Content-Disposition', 'attachment; filename="unassignFromQuiz.json"' );
        
        echo json_encode($response) ;
        
        $mainframe->close() ;
    }
    
    function notifyUser()
    {
        $mainframe =& JFactory::getApplication();
    	$quizModel =& JModel::getInstance('quiz', 'JQuarksModel') ;
    	
    	$data = JRequest::get('GET') ;
    	
    	if ($quizModel->notifyUser($data['quizId'], $data['userId'])) 
    	{
    		$response['message'] = JText::_( 'THE_USER_HAVE_BEEN_NOTIFIED' ) ;
            $response['type'] = "message" ;
    	} 
    	else
    	{
    	    $response['message'] = JText::_( 'ERROR_SENDING_NOTIFICATION' ) ;
            $response['type'] = "error" ;
    	}
    	
    	$document =& JFactory::getDocument();
        $document->setMimeEncoding( 'application/json' );
        JResponse::setHeader( 'Content-Disposition', 'attachment; filename="notifyUser.json"' );
        
        echo json_encode($response) ;
        
        $mainframe->close() ;
    }
    
    function save() 
    {
        JRequest::checkToken() or jexit( 'Invalid Token' );
        
        if ($this->_model->store()) 
        {
            $msg = JText::_( 'CANDIDATE_UPDATED' ) ;
            $type = "message" ;
        } 
        else 
        {
            $msg = JText::_( 'ERROR_UPDATING_CANDIDATE' ) ;
            $type = "error" ;           
        } 
        
        switch ($this->getTask()) 
        {
            case "save" :
                $link = 'index.php?option=com_jquarks&controller=candidates' ;
                break ;
            case "apply" :
                $link = 'index.php?option=com_jquarks&controller=candidate&display&cid[]=' . $this->_model->getId() ;
                break ;
        }
        
        $this->setRedirect($link, $msg, $type) ;
    }
    
    function cancel() 
    {       
        $this->setRedirect( 'index.php?option=com_jquarks&controller=candidates' ) ;
    }
}