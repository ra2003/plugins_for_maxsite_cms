<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * MaxSite CMS
 * (c) http://max-3000.com/
 */

  // это поле выводится только у страниц с заданным типом
  
  // массив $row содержит опции метаполя

  // флаг что тип наш
  $type_ok = false;

  // получим тип нужных страниц
  if (!isset($row['page_type'])) $row['page_type'] = '';
  
  if ($row['page_type'])
  {
     $CI = & get_instance();
	   $CI->db->select('page_id, page_slug, page_title , page_type_name ');
	   $CI->db->where('page_date_publish <', date('Y-m-d H:i:s'));
	   $CI->db->where('page_status', 'publish');
	   $CI->db->where('page_id', $page_id);
	   $CI->db->where('page_type_name', $row['page_type']);
	   $CI->db->join('page_type', 'page_type.page_type_id = page.page_type_id');
	   $CI->db->from('page');
	   $CI->db->order_by('page_id', 'random');
	
	   $query = $CI->db->get();
	
	   if ($query->num_rows() > 0)	// текущая страница нужного типа
        $type_ok = true;
  }
  else $type_ok = true;
   
if ($type_ok)  
{
	$cur_meta_id = 'f_meta_' . $options_key;
  $out = '<input type="text" id="' . $cur_meta_id . '" name="' . $name_f . '" value="' . $value . '">' . NR; 
   

  require_once (getinfo('plugins_dir') . 'file_manager/functions.php');


 // обработчик получения картинок в выбранной директории	
 $ajax_path = getinfo('ajax') . base64_encode('plugins/custom_meta/meta_types/type_file/meta-ajax.php');

	$allowed_types = (isset($row['allowed_types'])) ? $row['allowed_types'] : 'mp3|gif|jpg|jpeg|png|zip|txt|rar|doc|rtf|pdf|html|htm|css|xml|odt|avi|wmv|flv|swf|wav|xls|7z|gz|bz2|tgz';
	$hide_options = (isset($row['hide_options'])) ? $row['hide_options'] : '1';
	$show_size = (isset($row['show_size'])) ? $row['show_size'] : '1';
  



/* Thirdparty intialization scripts, needed for the Google Gears and BrowserPlus runtimes 
<script type="text/javascript" src="<?php echo $uploader_dir; ?>js/gears_init.js"></script>
<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script> -->
*/

	
	
	$uploader_dir = getinfo('plugins_url') . 'file_manager/uploader/';
	$tree_dir = getinfo('plugins_url') . 'file_manager/tree/';
	

	global $MSO;
	$allowed_types = str_replace ("|", ",", $allowed_types);

	$resize_images = (int) mso_get_option('resize_images', 'general', 600);
	if ($resize_images < 1) $resize_images = 600;
	
	$size_image_mini = (int) mso_get_option('size_image_mini', 'general', 150);
	if ($size_image_mini < 1) $size_image_mini = 150;

	$watermark_type = mso_get_option('watermark_type', 'general', 1);
	
	$mini_type = mso_get_option('image_mini_type', 'general', 1);	

  $out .=' <input type="hidden" id="f_ajax_path' . $options_key . '" value="' . $ajax_path . '">';
  
	// результат загрузки
	$out .= '<div id="results' . $options_key . '"><p><a href="javascript:void(0)" onclick="javascript:showresuts' . $options_key . '();" title="Показать/скрыть">Результаты загрузки</a></p>
	<div class="upl_message" id="upl_message' . $options_key . '"></div></div>';	

 // менеджер
	$out .= '<p><a href="javascript:void(0)" onclick="javascript:showdir' . $options_key . '();" title="Показать/скрыть">Выбрать</a></p>';
	$out .= '<div id="files_block' . $options_key . '" class="files_block">';
	
	// дерево
	$out .= '
	<div id="treeboxbox_tree_' . $options_key . '" setImagePath="' . getinfo('plugins_url') . 'file_manager/tree/csh_dhx_skyblue/" class="treebox" style="width:220px; height:250px; background-color:#f5f5f5;border :1px solid Silver; overflow:auto; margin-top:5px; float:left">';

	$k = getTreeFromCache();
	if($k){
		$out .= $k;
	} else {
		$k = getFoldersTree();
		$cache_key = 'fmtree_' . mso_md5($_SERVER['REQUEST_URI']);
		mso_add_cache($cache_key, $k, false, true);
		$out .= $k;	}
	
	$out .= '</div>';// дерево каталогов
	
  $out .= 	
	'<div class="files_list" id="files_list' . $options_key . '"></div>
	<div style="clear:both;"></div>
	<div id="files_tips' . $options_key . '" class="files_tips">Выберите файл</div>
  <hr class="br"><br></div>';	

// загрузчик

$out .='<div><a href="javascript: void(0);" title="Показать/скрыть" onclick="javascript:showuploader' . $options_key . '();">Загрузить</a></div>
	<div id="uploader_block' . $options_key . '">';


		
	// поле для передачи текущего директория
	$out .= '<input type="hidden" id="f_current_dir' . $options_key . '" class="current_dir" value="">';
	

	 
	 
	 // загрузчик файла
	$out .= '<div class="upload_file" style="margin-left:270px;">';


$out .= '
<script type="text/javascript">
// Convert divs to queue widgets when the DOM is ready

$(function() {
	$("#uploader_' . $options_key . '").pluploadQueue({
		// General settings
		runtimes : \'html5,html4\', //+ flash,silverlight,gears,browserplus
		url : \'' . getinfo('require-maxsite') . base64_encode('plugins/file_manager/uploader/upload-require-maxsite.php') . '\',
		max_file_size : \'' . ini_get('post_max_size') .'\',		

		// Specify what files to browse for
		filters : [
			{title : "Allowed files", extensions : "' . $allowed_types . '"}
		],

		//headers: {REFERER : \'http://max-latest.xp/admin/file_manager\'},
		
		// Flash settings
		//flash_swf_url : \'' . $uploader_dir . 'js/plupload.flash.swf\',

		// Silverlight settings
		//silverlight_xap_url : \'' . $uploader_dir . 'js/plupload.silverlight.xap\',
		
		// PreInit events, bound before any internal events
		preinit: {		
			Init: function(up, info) {
				if(info.runtime === \'html4\') up.settings.filters = false;
			},
			UploadFile: function(up, file) {	
				up.settings.multipart_params = {
					f_userfile_title		: document.getElementById("f_userfile_title").value,				
					f_userfile_resize		: document.getElementById("f_userfile_resize").checked ? \'1\' : \'\',							
					f_userfile_resize_size	: document.getElementById("f_userfile_resize_size").value,							
					f_userfile_mini			: document.getElementById("f_userfile_mini").checked ? \'1\' : \'\',							
					f_userfile_mini_size	: document.getElementById("f_userfile_mini_size").value,							
					f_mini_type				: document.getElementById("f_mini_type").value,							
					f_userfile_water		: (document.getElementById("f_userfile_water").checked && document.getElementById("f_userfile_water").disabled == false) ? \'1\' : \'\',							
					f_water_type			: document.getElementById("f_water_type").value,						
					f_session2_id			: document.getElementById("f_session2_id' . $options_key . '").value,
					f_directory				: \'' . getinfo('upoads_dir') . '\' + document.getElementById("f_current_dir' . $options_key . '").value
				};
			}
		},
		
		init: {
			StateChanged: function(up) {
				// Called when the state of the queue is changed
				if(up.state == 1){
					$(\'#file_upload\').submit();
				}
			},
			
			// Called when a file has finished uploading
			FileUploaded: function(up, file, info) {	
			
			  var upl_message = document.getElementById("upl_message' . $options_key . '");
			  var cur_dir = document.getElementById("f_current_dir' . $options_key . '").value;
			  var upl_url = \'' . getinfo('uploads_url') . '\' + cur_dir;
			  var title = document.getElementById("f_userfile_title");
			  var cod = upl_url + file.name;
			  var filename = "\'" + file.name + "\'"; 
			  var cur_meta = "f_meta_prev";
			  var prev = \'<a href="javascript:void(0)" onClick="selectfile' . $options_key . '(\'+ filename +\')"><img src="\' + upl_url + \'_mso_i/\' + file.name + \'" title="\'+ cod +\'"></a>\';
			  
				if(info.response == \'' . FM_GOOD_RESP . '\')
				{
					upl_message.innerHTML += \'<p>\' + prev + \'</p>\';
				} 
				else
				 if(info.response.indexOf(\'' . FM_GOOD_RESP . '\') + 1)
				 {
					upl_message.innerHTML += \'<div class="update">Файл \' + file.name + \' загрузился, но в ходе обработки возникли некоторые ошибки: </div>\' + info.response;			
				 } 
				 else
				 {
					upl_message.innerHTML += \'<div class="error">Файл \' + file.name + \' не загрузился из-за ошибок:\' + info.response + \'</div>\';
				 }	
				
				$("#results' . $options_key . '").fadeIn() ;	
				$(".plupload_buttons").fadeIn();
        $(".plupload_upload_status").fadeOut();		
				var load_tips = document.getElementById("load_tips' . $options_key . '");
			},
		}
	});

});

</script>


	<div id="uploader_' . $options_key . '" style="height:190px;">
		<p>You browser doesn\'t have Silverlight, Gears, BrowserPlus or HTML5 support.</p>
		<input type="hidden" name="lolo" value="123">
	</div>

';

// настройки	
$out .= '		
    <input type="hidden" id="f_session2_id' . $options_key . '" value="'. $MSO->data['session']['session_id'] .'">
		<div id="load_tips' . $options_key . '" class="load_tips">' . t('Добавьте файлы в список и нажмите "Загрузить". Загрузка произведется в выбранный каталог. Максимальный размер файла ', 'admin') . ' ' . ini_get ('post_max_size') . '.</div>
    <p><a href="javascript:void(0)" onclick="javascript:showsettings();" title="Показать/скрыть">Настройки обработки рисунков</a></p>

	</div>
</div>	
	';	

$out .= '		
    <div id="upload_settings' . $options_key . '" class="upload_settings">
		<p>' . t('Описание файла:', 'admin') . ' <input type="text" id="f_userfile_title" class="description_file" value=""></p>

		<p><label><input type="checkbox" id="f_userfile_resize" checked="checked" value=""> ' . t('Для изображений изменить размер до', 'admin') . '</label>
			<input type="text" id="f_userfile_resize_size" style="width: 50px" maxlength="4" value="' . $resize_images . '"> ' . t('px (по максимальной стороне).', 'admin') . '</p>

		<p><label><input type="checkbox" id="f_userfile_mini" checked="checked" value=""> ' . t('Для изображений сделать миниатюру размером', 'admin') . '</label>
			<input type="text" id="f_userfile_mini_size" style="width: 50px" maxlength="4" value="' . $size_image_mini . '"> ' . t('px (по максимальной стороне).', 'admin') . ' <br><em>' . t('Примечание: миниатюра будет создана в подкаталоге', 'admin') . ' <strong>mini</strong></em></p>


		<p>' . t('Миниатюру делать путем:', 'admin') . ' <select id="f_mini_type">
		<option value="1"'.(($mini_type == 1)?(' selected="selected"'):('')).'>' . t('Пропорционального уменьшения', 'admin') . '</option>
		<option value="2"'.(($mini_type == 2)?(' selected="selected"'):('')).'>' . t('Обрезки (crop) по центру', 'admin') . '</option>
		<option value="3"'.(($mini_type == 3)?(' selected="selected"'):('')).'>' . t('Обрезки (crop) с левого верхнего края', 'admin') . '</option>
		<option value="4"'.(($mini_type == 4)?(' selected="selected"'):('')).'>' . t('Обрезки (crop) с левого нижнего края', 'admin') . '</option>
		<option value="5"'.(($mini_type == 5)?(' selected="selected"'):('')).'>' . t('Обрезки (crop) с правого верхнего края', 'admin') . '</option>
		<option value="6"'.(($mini_type == 6)?(' selected="selected"'):('')).'>' . t('Обрезки (crop) с правого нижнего края', 'admin') . '</option>
		<option value="7"'.(($mini_type == 7)?(' selected="selected"'):('')).'>' . t('Уменьшения и обрезки (crop) в квадрат', 'admin') . '</option>
		</select></p>

		<p><label><input type="checkbox" id="f_userfile_water" value="" '.
			((file_exists(getinfo('uploads_dir') . 'watermark.png')) ? '' : ' disabled="disabled"') . 
			((mso_get_option('use_watermark', 'general', 0)) ? (' checked="checked"') : ('')) .
			'> ' . t('Для изображений установить водяной знак', 'admin') . '
			<select id="f_water_type">
			<option value="1"'.(($watermark_type == 1)?(' selected="selected"'):('')).'>' . t('По центру', 'admin') . '</option>
			<option value="2"'.(($watermark_type == 2)?(' selected="selected"'):('')).'>' . t('В левом верхнем углу', 'admin') . '</option>
			<option value="3"'.(($watermark_type == 3)?(' selected="selected"'):('')).'>' . t('В правом верхнем углу', 'admin') . '</option>
			<option value="4"'.(($watermark_type == 4)?(' selected="selected"'):('')).'>' . t('В левом нижнем углу', 'admin') . '</option>
			<option value="5"'.(($watermark_type == 5)?(' selected="selected"'):('')).'>' . t('В правом нижнем углу', 'admin') . '</option>
			</select>
			</label>
			<br><em>' . t('Примечание: водяной знак должен быть файлом <strong>watermark.png</strong> и находиться в каталоге', 'admin') . ' <strong>uploads</strong></em></p>
		</div>';

$out .= '
<script type="text/javascript">
    	var tree = dhtmlXTreeFromHTML("treeboxbox_tree_' . $options_key . '"); 
			tree.checkbox = true;
			tree.setOnClickHandler(tonclick' . $options_key . ');
			
        $("#uploader_block' . $options_key . '").fadeToggle() ;
        $("#files_block' . $options_key . '").fadeToggle() ;
        $("#upload_settings' . $options_key . '").fadeToggle() ;
        $("#results' . $options_key . '").fadeToggle() ;
		
		showfiles' . $options_key . '();	
		
// показать файлы в каталоге
function showfiles' . $options_key . '(id){
	 // не мешало бы проверить - открыт ли блок с файлами 		  
	  var ajax_path = $("#f_ajax_path' . $options_key . '").val();
	  var data = \'type=get_files&dir=\' + id + \'&meta=' . $options_key . '\'; 
	  var result = $("#files_list' . $options_key . '");
	  
		$.ajax({
			type: \'POST\',
			dataType: \'json\',
			url: ajax_path,
			data: data,
			beforeSend:
				function(){
				},
			success:
				function(json,textStatus){
					// Если произошла ошибка
					if( 1 == json.error_code){
					  result.html(json.error_description);
					}
					else{
					  files = json.resp;
						result.html(files);
					}
				},
			error:
				function(){
					result.html(\'Ошибка\');
				}
		});
}

function tonclick' . $options_key . '(id){
				if(id == \'uploads\')
				{ 
				  id = \'\';
			    document.getElementById("f_current_dir' . $options_key . '").value = \'\';
			  }
			  else
			  {
			     id = id + \'/\';
			     document.getElementById("f_current_dir' . $options_key . '").value = id;
			  }
	 
	 showfiles' . $options_key . '(id);
	  $("#files_tips' . $options_key . '").fadeOut() ;
	 
}; 

function selectfile' . $options_key . '(file) {
  var content = document.getElementById("f_meta_' . $options_key . '");
  var out = file;
  content.value = \'uploads/\' + out;

}	

function showuploader' . $options_key . '(){
       $("#uploader_block' . $options_key . '").fadeToggle() ;
       }	
       
function showdir' . $options_key . '(){
       $("#files_block' . $options_key . '").fadeToggle() ;
       }	       

function showsettings' . $options_key . '(){
       $("#upload_settings' . $options_key . '").fadeToggle() ;
       }
       
function showresuts' . $options_key . '(){
       $("#upl_message' . $options_key . '").fadeToggle() ;
       }    		
</script>
';
   
   
  $f .= $out . NR; 
   
}

?>