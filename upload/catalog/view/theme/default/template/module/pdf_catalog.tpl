<div class="box">

<div class="box-heading">
<img src="image/data/pdf.png" alt="" />
<?php echo $heading_title; ?>
</div>

<div class="box-content" style="text-align: center;">


<?php
if(!empty($categories))
{
?>

<select style="width:160px;" onchange="fn_pdf_category(this.options[this.selectedIndex].value);">
<option value=""><?php echo $text_select; ?></option>


<?php
foreach($categories as $key => $category)
{
?>

<option value="<?php echo $category['category_id']?>"><?php echo $category['name'];?></option>

<?php
}
?>

</select>



<?php
}else{
?>
<input type="button" class="button" value="
<?php echo $text_all_categories; ?>" onclick="fn_pdf_category('0');" id="pdf_button" name="pdf_button">
</input>

<?php
}
?>


</div>
<script type="text/javascript">
function fn_pdf_category(category_id)
{
if(category_id != "")
{
window.open('<?php echo $pdf_catalog_href;?>'+category_id, 'window_pdf', 'width=800, height=600, toolbar=1, resizable=1');
}
}
</script>
</div>



