<?php
/**
 * JQuarks Component Questions Controller
 * 
 * @version		$Id: questions.php 67 2010-03-10 08:40:10Z fnaccache $
 * @author		IP-Tech Labs <labs@iptech-offshore.com> 
 * @copyright	2009-2010 IP-Tech
 * @package     JQuarks-Back-Office
 * @subpackage  Controllers
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

jimport('joomla.application.component.controller');


class JQuarksControllerQuestions extends JQuarksController 
{
	function __construct() 
	{
		parent::__construct();

		$this->registerTask( 'add', 'edit' ) ;
		$this->registerTask( 'copy', 'edit' ) ;	
		$this->registerTask( 'saveContinue', 'save' ) ;
	}
	
	
	function edit($id = null) 
	{
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;
		
		if ($id == null) {
			$id = $cids[0] ;
		}
		
		if ($this->getTask() == 'copy') {
			$link = 'index.php?option=com_jquarks&view=question&task=copy&cid[]=' . $id ;
		} else { 	
			$link = 'index.php?option=com_jquarks&view=question&cid[]=' . $id ;
		}
		
		$this->setRedirect($link) ;
	}	
	
	/**
	 * Deleting questions or archiving them according to them having been answered or not
	 * 
	 * @TODO take care of the labels in this methods
	 */  
	function remove() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );		
		
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' ) ;

		$model = $this->getModel('questions') ;
		
		$incoherentRandomSets = $model->checkRandomDependencies($cids) ;
		$emptyCustomSets = $model->checkCustomDependencies($cids) ;
		
		if (count($incoherentRandomSets)) 
		{
			$notice = JText::_( 'DELETING_QUESTION_WILL_CAUSE_INCOHERENT_RANDOM_SETS' ) . '<br /><br />' ;
			$notice .= JText::_( 'AFFECTED_RANDOM_SETS_ARE') ;
			
			foreach ($incoherentRandomSets AS $incoherentRandomSet) {
				$notice .= '<p style="text-indent:30px;">"' . $incoherentRandomSet->title . '"</p>' ;
			}
			
			JError::raiseNotice('', $notice);
			
			$msg = JText::_( 'RANDOM_SETS_INCOHERENT' ) ;
			$type = "error" ;
		} 
		elseif(count($emptyCustomSets)) 
		{
			
			$notice = JText::_( 'DELETING_QUESTION_WILL_CAUSE_EMPTY_CUSTOM_SETS' ) . '<br /><br />' ;
			$notice .= JText::_( 'AFFECTED_EMPTY_SETS_ARE') ;
			
			foreach ($emptyCustomSets AS $emptyCustomSet) {
				$notice .= '<p style="text-indent:30px;">"' . $emptyCustomSet->title . '"</p>' ;
			}
			
			JError::raiseNotice('', $notice);
			
			$msg = JText::_( 'CUSTOM_SETS_EMPTY' ) ;
			$type = "error" ;
		} 
		else 
		{	
			if ($model->delete($cids)) 
			{
				$msg = JText::_( 'QUESTION(S)_DELETED' ) ;
				$type = "message" ;
			} 
			else 
			{
				$msg = JText::_( 'ERROR_DELETING_QUESTION(S)' ) ;
				$type = "error" ;
			}
		} 
		
		$link = 'index.php?option=com_jquarks&view=questions' ;
		$this->setRedirect($link, $msg, $type) ;
	}

    function save() 
    {       
        JRequest::checkToken() or jexit( 'Invalid Token' );
        
        $model = $this->getModel('question') ;
        
        if ($model->store()) 
        {
            $msg = JText::_( 'QUESTION_SAVED' ) ;
            $type = "message" ;
        } 
        else 
        {
            $msg = JText::_( 'ERROR_COULD_NOT_CREATE_QUESTION' ) ;
            $type = "error" ;           
        } 
        
        switch ($this->getTask()) 
        {
            case "save" :
                $link = 'index.php?option=com_jquarks&view=questions' ;
                break ;
            case "apply" :
                $link = 'index.php?option=com_jquarks&view=question&task=edit&cid[]=' . $model->getId() ;
                break ;
            case "saveContinue" :
                $link = 'index.php?option=com_jquarks&view=question&task=edit&cid[]=0' ;
                break ;
        }
        
        $this->setRedirect($link, $msg, $type) ;
    }

    function addCategory() 
    {
        $mainframe =& JFactory::getApplication();
        $categoryModel =& JModel::getInstance('category','JQuarksModel');
        $category = $categoryModel->addCategory() ;
        
        if ($category) {
            $return = json_encode($category) ;
        } else {
            $return = "{ \"id\" : 0 }" ;
        }
        echo $return ;
        
        $mainframe->close();
    }
    
    function removeCategory() 
    {
        $mainframe =& JFactory::getApplication();
        $categoryModel =& JModel::getInstance('category','JQuarksModel');
        $category = $categoryModel->removeCategory() ;
        
        if ($category) {
            $return = json_encode($category) ;
        } else {
            $return = "{ \"id\" : 0 }" ;
        }
        echo $return ;
        $mainframe->close();
    }
    
    function cancel() 
    {
        $this->setRedirect( 'index.php?option=com_jquarks&view=questions' ) ;
    }
    

    function insertCodeHighlight()
    {
        $eName  = JRequest::getVar('e_name');
        $eName  = preg_replace( '#[^A-Z0-9\-\_\[\]]#i', '', $eName );
        ?>
        <script type="text/javascript">
        
            function insertCodeBalise() 
            {
                var content = window.parent.tinyMCE.activeEditor.selection.getContent();
                var language = document.getElementById("lang").value ;

                if (content && language) 
                    window.parent.jInsertEditorText('<pre name=\"code\" class=\"'+ language +'\">' + content + '</pre>', '<?php echo $eName; ?>');

                /*if (content && language) 
                    window.parent.jInsertEditorText('<textarea name=\"code\" class=\"'+ language +'\" cols="60" row="10" >' + content + '</textarea>', '<?php echo $eName; ?>');
                */

                window.parent.document.getElementById('sbox-window').close();
                return false ;
            }
        </script>

        <form>
            <fieldset style="margin:auto">
            <legend><?php echo JText::_( 'CODE_HIGHLIGHTING' )?></legend>
                <div style="text-align:center">
                    <label for="lang">
                        <?php echo JText::_( 'LANGUAGE' ); ?> :
                    </label>
                    <select id="lang">
                        <option value=""><?php echo JText::_( 'SELECT_LANGUAGE' ); ?></option>
                        <option value="cpp">C++</option>
                        <option value="csharp">C#</option>
                        <option value="css">CSS</option>
                        <option value="delphi">Delphi</option>
                        <option value="java">Java</option>
                        <option value="js">Javascript</option>
                        <option value="php">PHP</option>
                        <option value="python">Python</option>
                        <option value="ruby">Ruby</option>
                        <option value="sql">Sql</option>
                        <option value="vb">VB</option>
                        <option value="xhtml">XML/HTML</option>
                        <?php /* 
                            languages and their aliases
                             
                            C++         cpp, c, c++
                            C#          c#, c-sharp, csharp
                            CSS         css
                            Delphi      delphi, pascal
                            Java        java
                            Java Script js, jscript, javascript
                            PHP         php
                            Python      py, python
                            Ruby        rb, ruby, rails, ror
                            Sql         sql
                            VB          vb, vb.net
                            XML/HTML    xml, html, xhtml, xslt
                        */?>
                    </select>
                    <button onclick="insertCodeBalise();"><?php echo JText::_( 'HIGHLIGHT' ); ?></button>
                </div>  
            </fieldset> 
        </form>
        <?php
    }
	
}