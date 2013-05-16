<?php
/**
 * JQuarks Component Question Controller
 * 
 * @version		$Id: question.php 59 2010-03-02 08:40:27Z fnaccache $
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


class JQuarksControllerQuestion extends JQuarksController 
{
	public $_model ;
	
	function __construct() 
	{
		parent::__construct();
		
		$this->_model = $this->getModel('question');
		
		$this->registerTask( 'apply', 'save' );
		$this->registerTask( 'saveContinue', 'save' );
	}
	
	function display() 
	{
		JRequest::setVar( 'hidemainmenu', 1 );
		JRequest::setVar('view','question') ;
		
		parent::display() ;
	}
	
	function save() 
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		if ($this->_model->store()) 
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
				$link = 'index.php?option=com_jquarks&controller=questions' ;
				break ;
			case "apply" :
				$link = 'index.php?option=com_jquarks&controller=question&task=edit&cid[]=' . $this->_model->getId() ;
				break ;
			case "saveContinue" :
				$link = 'index.php?option=com_jquarks&controller=question&task=edit&cid[]=0' ;
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
	
	function removeCategory() {
		
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
		$this->setRedirect( 'index.php?option=com_jquarks&controller=questions' ) ;
	}
	

	function insertCodeHighlight()
	{
		$eName	= JRequest::getVar('e_name');
		$eName	= preg_replace( '#[^A-Z0-9\-\_\[\]]#i', '', $eName );
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
							 
							C++ 		cpp, c, c++
							C# 			c#, c-sharp, csharp
							CSS 		css
							Delphi 		delphi, pascal
							Java 		java
							Java Script js, jscript, javascript
							PHP 		php
							Python 		py, python
							Ruby 		rb, ruby, rails, ror
							Sql 		sql
							VB 			vb, vb.net
							XML/HTML 	xml, html, xhtml, xslt
						*/?>
					</select>
					<button onclick="insertCodeBalise();"><?php echo JText::_( 'HIGHLIGHT' ); ?></button>
				</div>	
			</fieldset>	
		</form>
		<?php
	}
	
}