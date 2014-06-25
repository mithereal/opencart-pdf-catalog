<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<div class="box">
  <div class="heading">
    <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?></h1>
    <div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>
  </div>
  <div class="content">
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <table id="module" class="form ">
                 <thead>
            <tr>
              <td class="left"><?php echo $entry_layout; ?></td>
              <td class="left"><?php echo $entry_position; ?></td>
              <td class="left"><?php echo $entry_status; ?></td>
              <td class="right"><?php echo $entry_sort_order; ?></td>
              <td></td>
            </tr>
          </thead>
          <?php $module_row = 0; 
         // var_dump($modules); 
         ?>
          <?php foreach ($modules as $module) { ?>
          <tbody id="module-row<?php echo $module_row; ?>">
            <tr>
              <td class="left"><select name="pdf_catalog_module[<?php echo $module_row; ?>][layout_id]">
                  <?php foreach ($layouts as $layout) { ?>
                  <?php if ($layout['layout_id'] == $module['layout_id']) { ?>
                  <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select></td>
              <td class="left"><select name="pdf_catalog_module[<?php echo $module_row; ?>][position]">
                  <?php if ($module['position'] == 'content_top') { ?>
                  <option value="content_top" selected="selected"><?php echo $text_content_top; ?></option>
                  <?php } else { ?>
                  <option value="content_top"><?php echo $text_content_top; ?></option>
                  <?php } ?>
                  <?php if ($module['position'] == 'content_bottom') { ?>
                  <option value="content_bottom" selected="selected"><?php echo $text_content_bottom; ?></option>
                  <?php } else { ?>
                  <option value="content_bottom"><?php echo $text_content_bottom; ?></option>
                  <?php } ?>
                  <?php if ($module['position'] == 'column_left') { ?>
                  <option value="column_left" selected="selected"><?php echo $text_column_left; ?></option>
                  <?php } else { ?>
                  <option value="column_left"><?php echo $text_column_left; ?></option>
                  <?php } ?>
                  <?php if ($module['position'] == 'column_right') { ?>
                  <option value="column_right" selected="selected"><?php echo $text_column_right; ?></option>
                  <?php } else { ?>
                  <option value="column_right"><?php echo $text_column_right; ?></option>
                  <?php } ?>
                </select></td>
              <td class="left"><select name="pdf_catalog_module[<?php echo $module_row; ?>][status]">
                  <?php if ($module['status']) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select></td>
              <td class="right"><input type="text" name="pdf_catalog_module[<?php echo $module_row; ?>][sort_order]" value="<?php echo $module['sort_order']; ?>" size="3" /></td>
              <td class="left"><a onclick="$('#module-row<?php echo $module_row; ?>').remove();" class="button"><?php echo $button_remove; ?></a></td>
            </tr>
          </tbody>
          <?php $module_row++; ?>
          <?php } ?>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="left"><a onclick="addModule();" class="button"><?php echo $button_add_module; ?></a></td>
            </tr>
          </tfoot>
          </table>
          <table id="module" class="form ">

	  <!-- 
		<tr>
          <td><?php echo $entry_pdf_catalog_template_type; ?></td>
          <td><select name="pdf_catalog_template_type">
              <?php if ($pdf_catalog_template_type == "native") { ?>
              <option value="html">HTML</option>
              <option value="native" selected="selected"><?php echo $text_native; ?></option>
              <?php } else { ?>
             <option value="html" selected="selected">HTML</option>
              <option value="native"><?php echo $text_native; ?></option>
              <?php } ?>
            </select></td>
        </tr>
   -->
	   <?php
	   if($tcpdf == false){
		   ?>
		<tr id="tcpdf">
          <td class="left"><a onclick="install_Tcpdf();" class="button"><?php echo $button_install_tcpdf; ?></td>
        </tr>
		
	   <div id="result">
          
      </div>
  <?php
	}
	?>
	<tr>
          <td> <h2>Frontend</h2></td>
        </tr>
		<tr>
          <td><?php echo $entry_display_categories; ?></td>
          <td><select name="pdf_catalog_display_categories">
              <?php if ($pdf_catalog_display_categories) { ?>
              <option value="1" selected="selected"><?php echo $text_yes; ?></option>
              <option value="0"><?php echo $text_no; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_yes; ?></option>
              <option value="0" selected="selected"><?php echo $text_no; ?></option>
              <?php } ?>
            </select></td>
        </tr>
		<tr>
          <td><?php echo $entry_display_subcategories; ?></td>
          <td><select name="pdf_catalog_display_subcategories">
              <?php if ($pdf_catalog_display_subcategories) { ?>
              <option value="1" selected="selected"><?php echo $text_yes; ?></option>
              <option value="0"><?php echo $text_no; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_yes; ?></option>
              <option value="0" selected="selected"><?php echo $text_no; ?></option>
              <?php } ?>
            </select></td>
        </tr>
      
        <tr>
          <td> <h2>PDF</h2></td>
        </tr>
		<tr>
          <td><?php echo $entry_display_toc; ?></td>
          <td><select name="pdf_catalog_display_toc">
              <?php if ($pdf_catalog_display_toc) { ?>
              <option value="1" selected="selected"><?php echo $text_yes; ?></option>
              <option value="0"><?php echo $text_no; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_yes; ?></option>
              <option value="0" selected="selected"><?php echo $text_no; ?></option>
              <?php } ?>
            </select></td>
        </tr>
		<tr>
          <td><?php echo $entry_display_out_of_stock; ?></td>
          <td><select name="pdf_catalog_display_out_of_stock">
              <?php if ($pdf_catalog_display_out_of_stock) { ?>
              <option value="1" selected="selected"><?php echo $text_yes; ?></option>
              <option value="0"><?php echo $text_no; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_yes; ?></option>
              <option value="0" selected="selected"><?php echo $text_no; ?></option>
              <?php } ?>
            </select></td>
        </tr>
		<tr>
          <td><?php echo $entry_display_disabled; ?></td>
          <td><select name="pdf_catalog_display_disabled">
              <?php if ($pdf_catalog_display_disabled) { ?>
              <option value="1" selected="selected"><?php echo $text_yes; ?></option>
              <option value="0"><?php echo $text_no; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_yes; ?></option>
              <option value="0" selected="selected"><?php echo $text_no; ?></option>
              <?php } ?>
            </select></td>
        </tr>
        <!--
		<tr>
          <td><?php echo $entry_sort_products; ?></td>
          <td><select name="pdf_catalog_sort_products">
              <?php if ($pdf_catalog_sort_products == 'pd.name') { ?>
              <option value="pd.name" selected="selected"><?php echo $text_product_name; ?></option>
              <option value="quantitytotal"><?php echo $text_product_popular; ?></option>
              <?php } else { ?>
              <option value="pd.name"><?php echo $text_product_name; ?></option>
              <option value="quantitytotal" selected="selected"><?php echo $text_product_popular; ?></option>
              <?php } ?>
            </select></td>
        </tr>
        -->
	      <tr>
          <td><?php echo $entry_pdf_max_products; ?> </td>
              <td><input type="text" name="pdf_catalog_max_products" value="<?php echo $pdf_catalog_max_products;?>"/></td>
	  </tr>
	      <tr>
          <td><?php echo $entry_pdf_max_options; ?> </td>
              <td><input type="text" name="pdf_catalog_max_options" value="<?php echo $pdf_catalog_max_options;?>"/></td>
	  </tr>
          <td><?php echo $entry_pdf_max_per_options; ?> </td>
              <td><input type="text" name="pdf_catalog_max_per_options" value="<?php echo $pdf_catalog_max_per_options;?>"/></td>
	  </tr>
	      <tr>
          <td><?php echo $entry_pdf_author; ?> </td>
              <td><input type="text" name="pdf_catalog_author" value="<?php echo $pdf_catalog_author;?>"/></td>
	  </tr>
	  <tr>
          <td><?php echo $entry_pdf_title; ?> </td>
              <td><input type="text" name="pdf_catalog_title" value="<?php echo $pdf_catalog_title;?>"/></td>
	  </tr>
	      <tr>
          <td><?php echo $entry_pdf_subject; ?> </td>
              <td><input type="text" name="pdf_catalog_subject" value="<?php echo $pdf_catalog_subject;?>"/></td>
	  </tr>
	      <tr>
          <td><?php echo $entry_pdf_keywords; ?> </td>
             <td> <input type="text" name="pdf_catalog_keywords" value="<?php echo $pdf_catalog_keywords;?>"/></td>
	  </tr>
	      <tr>
          <td><?php echo $entry_pdf_catalog_image_height; ?> </td>
             <td> <input type="text" name="pdf_catalog_image_height" value="<?php echo $pdf_catalog_image_height;?>"/></td>
	  </tr>
	      <tr>
          <td><?php echo $entry_pdf_catalog_image_width; ?> </td>
             <td> <input type="text" name="pdf_catalog_image_width" value="<?php echo $pdf_catalog_image_width;?>"/></td>
	  </tr>
	      <tr>
          <td><?php echo $entry_pdf_catalog_item_per_page; ?> </td>
             <td> <input type="text" name="pdf_catalog_item_per_page" value="<?php echo $pdf_catalog_item_per_page;?>"/></td>
	   
          <tr>
          <td><?php echo $entry_pdf_catalog_display_description; ?></td>
          <td><select name="pdf_catalog_display_description">
              <?php if ($pdf_catalog_display_description) { ?>
              <option value="1" selected="selected"><?php echo $text_yes; ?></option>
              <option value="0"><?php echo $text_no; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_yes; ?></option>
              <option value="0" selected="selected"><?php echo $text_no; ?></option>
              <?php } ?>
            </select></td>
            <td><?php echo $entry_pdf_catalog_description_chars; ?>
                <input type="text" name="pdf_catalog_description_chars" value="<?php echo $pdf_catalog_description_chars;?>"/></td>
        </tr>
	
        <tr>
        <td><?php echo $entry_description; ?></td>
        <td>
          <textarea name="pdf_catalog_description" id="pdf_catalog_description"><?php echo $pdf_catalog_description;?></textarea>
	</td>
        </tr>
        
      </table>
    </form>
  </div>
</div>
</div>

<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script>
<script type="text/javascript"><!--
CKEDITOR.replace('pdf_catalog_description', {
	filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});
//--></script>

<script type="text/javascript"><!--
var module_row = <?php echo $module_row; ?>;

function install_Tcpdf() {	
$( '#result' ).html( "Installing Tcpdf Please Wait..." );
$( '#tcpdf' ).hide();
$.get( "index.php?route=module/pdf_catalog/fetch_api&token=<?php echo $token; ?>", function( data ) {
$( '#result' ).html( data );
$( '#result' ).slideUp(7500);

});
}

function addModule() {	
	html  = '<tbody id="module-row' + module_row + '">';
	html += '  <tr>';
	html += '    <td class="left"><select name="pdf_catalog_module[' + module_row + '][layout_id]">';
	<?php foreach ($layouts as $layout) { ?>
	html += '      <option value="<?php echo $layout['layout_id']; ?>"><?php echo addslashes($layout['name']); ?></option>';
	<?php } ?>
	html += '    </select></td>';
	html += '    <td class="left"><select name="pdf_catalog_module[' + module_row + '][position]">';
	html += '      <option value="content_top"><?php echo $text_content_top; ?></option>';
	html += '      <option value="content_bottom"><?php echo $text_content_bottom; ?></option>';
	html += '      <option value="column_left"><?php echo $text_column_left; ?></option>';
	html += '      <option value="column_right"><?php echo $text_column_right; ?></option>';
	html += '    </select></td>';
	html += '    <td class="left"><select name="pdf_catalog_module[' + module_row + '][status]">';
    html += '      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
    html += '      <option value="0"><?php echo $text_disabled; ?></option>';
    html += '    </select></td>';
	html += '    <td class="right"><input type="text" name="pdf_catalog_module[' + module_row + '][sort_order]" value="" size="3" /></td>';
	html += '    <td class="left"><a onclick="$(\'#module-row' + module_row + '\').remove();" class="button"><?php echo $button_remove; ?></a></td>';
	html += '  </tr>';
	html += '</tbody>';
	
	$('#module tfoot').before(html);
	
	module_row++;
}
//--></script> 

<?php echo $footer; ?>
