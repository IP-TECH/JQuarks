<?php

            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');

            $site = JURI::root() ;
            $exist = false;

            $jfpath  = JPATH_ADMINISTRATOR . DS .'components' . DS . 'com_joomfish'. DS .'contentelements';
            $xmlpath = JPATH_ADMINISTRATOR . DS .'components' . DS . 'com_jquarks'. DS .'joomfish_contentelements';

            // copy xml files if joomfish is installed
            if (JFolder::exists($jfpath))
            {
                $exist = true;

                $xmlfiles = JFolder::files($xmlpath);

                foreach ($xmlfiles as $file)
                {
                    if($file != 'index.html')
                    {
                        $success = JFile::copy($xmlpath . DS . $file, $jfpath . DS . $file);
                        if(!$success)
                            $exist = false;
                    }

                }
            }

?>
<h1>Welcome to JQuarks</h1> 
<p>
	JQuarks is an easy to use Quiz manager for the Joomla CMS :
</p>
	<ol>
		<li>Create your Categories</li>
		<li>Create your Questions (Multiple/Unique Choices and Free Answers)</li>
		<li>Build Custom and Random Sets of Questions</li>
		<li>Build your Quiz with the sets you created</li>
		<li>Configure your Quiz (Pagination, Publication time, Users Affectations)</li>
		<li>Consult the answers/ Export them in CSV</li>
	</ol>

    <?php 
            if($exist)
                echo "<p><small>Compatibilty files for Joomfish have been successfully copied</small></p>" ;
    ?>

<p>	
	<strong>JQuarks is powered by</strong><br /> 
	<a href="http://www.iptechinside.com/labs/projects/show/jquarks" title="IP-Tech"><img src="<?php echo $site ?>administrator/components/com_jquarks/assets/images/iptech.png" alt="IP-Tech Logo" title="IP-Tech"></a>
</p>