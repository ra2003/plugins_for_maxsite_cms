<?
if (file_exists($MSO->config['templates_dir'] . $MSO->config['template'] .'/'. $file.'.php')){
	include($file.'.php');
}else{
	$MSO->config['templates_dir'] = $old_path_templates;
?>