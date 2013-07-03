<?php
	define('PDF_CATALOG_TEMPLATE_CSS', '
		<style>
			table.pdf_table {
				width:100%;
			}
			
			table.pdf_table td.pdf_category{
				font-size:17pt;
				background-color:#EFEFEF;
				padding:20px;
			}
			
			tr.pdf_product{
				page-break-inside:avoid; 
				page-break-after:auto;
				height: {::image_height}px;
			}
			
			td.pdf_product_image{
				width:30%;
				text-align:center;
				padding: 0;
				margin: 0;
				vertical-align: middle;
				border-bottom: 1px solid #EFEFEF;
			}
			
			td.pdf_product_desc{
				width:70%;
				padding:10px;
				border-left: 1px solid #EFEFEF;
				border-bottom: 1px solid #EFEFEF;
			}
			
			ul.pdf_product_ul{
				list-style-type:none;
				margin:0;
				padding:0;
			}
			
			.page_break{
				page-break-before: always;
			}
		</style>
		
	');
	
	define('PDF_TOC_TITLE', '
		<h1>{::category_title}</h1><br>
	');
	
	define('PDF_TOC_CATEGORY', '
		<h2>{::category_name} ({::product_cnt})</h2><br>
	');
	
	define('PDF_CATALOG_TEMPLATE_CATEGORY', '
		<table class="pdf_table" cellpadding="2px" cellspacing="0">
		<tr nobr="true">
			<td colspan="2" class="pdf_category">{::category_name}</td>
		</tr>
		{::products}
		</table>
	');
	
	define('PDF_CATALOG_TEMPLATE_PRODUCT', '
		<tr class="pdf_product" nobr="true">
			<td class="pdf_product_image"><img src="{::product_image}" class="product_image" /></td>
			<td class="pdf_product_desc">
				<ul class="pdf_product_ul">
					<li class="pdf_product_name"><strong>{::txt_product_name}</strong> {::product_name}</li>
					<li><strong>{::txt_prdocut_model}</strong> {::product_model}</li>
					<li><strong>{::txt_product_price}</strong> {::product_price}</li>
					<li><strong>{::txt_product_description}</strong> {::product_description}</li>
				</ul>
			</td>
		</tr>
	');
?>
