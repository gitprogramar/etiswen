<?php 
// No direct access
defined('_JEXEC') or die; ?>
<?php
if(strpos($html, "<?php") === false)
{
	echo $html;
}
echo $module->content;
?>
