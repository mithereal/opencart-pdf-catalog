<?php
	class ControllerProductPdfcatalog extends Controller {  
		public function index() { 
			$this->load->config('pdf_catalog');
			$this->load->model('catalog/pdf_catalog');
			$this->load->language('module/pdf_catalog');
			
			
			$this->data['entry_position'] = $this->language->get('entry_position');
			
			$this->load->model('tool/image');
			if(!isset($this->request->get['category_id']) || $this->request->get['category_id'] == "0")
			{
				$categories = $this->model_catalog_pdf_catalog->getCategories(0);
				$data = array(
					  'filter_status' 	=>	1
					, 'sort'	=>	'pd.name'
				);
				foreach($categories as $key => $category)
				{
					$products = $this->model_catalog_pdf_catalog->getProductsByCategoryId($category['category_id'], $data);
					if(!empty($products))
					{
						foreach($products as $key2 => $product)
						{
							$products[$key2]['price'] = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));		
						}	
					}
					$categories[$key]['products'] = $products;
					
				}
				
				$this->createPdf($categories);
			}
			elseif($this->request->get['category_id'] != "0")
			{
				$category = $this->model_catalog_pdf_catalog->getCategory($this->request->get['category_id']);
				$data = array(
					  'status' 	=>	1
					, 'sort'	=>	'pd.name'
				);
				$products = $this->model_catalog_pdf_catalog->getProductsByCategoryId($category['category_id'], $data);
				if(!empty($products))
				{
					foreach($products as $key2 => $product)
					{
						$products[$key2]['price'] = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));		
					}	
				}
				$category['products'] = $products;
				
				$this->createPdf(array(0=>$category));
			}
			
		}
		
	
		private function createPdf($pdf_data){
			
			$image_width = 100;
			$image_height = 100;
			
			$item_per_page = 600/$image_height;
			
			
			$this->load->helper('tcpdf/config/lang/eng');
			$this->load->helper('tcpdf/tcpdf');
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$author=$this->config->get('pdf_catalog_author');
			$title=$this->config->get('pdf_catalog_title');
			$subject=$this->config->get('pdf_catalog_subject');
			$keywords=$this->config->get('pdf_catalog_description');
			// set document information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor($author);
			$pdf->SetTitle($title);
			$pdf->SetSubject($subject);
			$pdf->SetKeywords($keywords);
			
			// set default header data
			$pdf_logo = "../../../image/".$this->config->get('config_logo');
			if(@file_exists($pdf_logo) == false)
			{
				$pdf_logo = "../../../image/data/pdf_catalog_default_logo.png";
			}
			$pdf_title = $this->config->get('config_name');
			$pdf_string = $this->config->get('config_url');
			$pdf->SetHeaderData('../'.$pdf_logo, PDF_HEADER_LOGO_WIDTH, $pdf_title, $pdf_string);
			
			// set header and footer fonts
			$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
			$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
			
			// set default monospaced font
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
			
			//set margins
			$pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);
			$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			
			//set auto page breaks
			$pdf->SetAutoPageBreak(TRUE, 20);
			// ---------------------------------------------------------
			// set font
			$pdf->SetFont('helvetica', '', 10);
			
			// add a page
			$pdf->AddPage();
			
			$html_template_css = PDF_CATALOG_TEMPLATE_CSS;
			$html_template_category = PDF_CATALOG_TEMPLATE_CATEGORY;
			$html_template_product = PDF_CATALOG_TEMPLATE_PRODUCT;
			
			$html_template_css = str_replace("{::image_height}", $image_height, $html_template_css);
			$html = $html_template_css;
			//$pdf->writeHTML($html_template_css, true, false, true, false, '');
			
			//echo $html_template_css;
			// define some HTML content with style
		 
			if($this->config->get('pdf_catalog_description') && strlen(trim($this->config->get('pdf_catalog_description'))) >1)
			{
				$html_description  = '<div>'.html_entity_decode($this->config->get('pdf_catalog_description')).'</div><div class="page_break"></div>';
				//$pdf->writeHTML($html_description, true, false, true, false, '');
				$html .= $html_description;
				//echo $html;	
			}
			
			$no_of_category = 0;
			if($this->config->get('pdf_catalog_display_categories') == 1)
			{
				if(!empty($pdf_data))
				{
					$html_catalog_toc = PDF_TOC_CATEGORY;
					$catalog_toc = PDF_TOC_TITLE;
					$catalog_toc = str_replace("{::category_title}", $this->language->get('text_category_title'), $catalog_toc);  
					foreach($pdf_data as $key => $category)
					{
						$tmp_catalog_toc  = str_replace("{::category_name}", $category['name'], $html_catalog_toc);
						$tmp_catalog_toc  = str_replace("{::product_cnt}", count($category['products']), $tmp_catalog_toc);
						$catalog_toc .= $tmp_catalog_toc;
						$no_of_category++;
					}
				}
				
				if($no_of_category > 0)
				{
					$catalog_toc .= '<div class="page_break"></div>';
					$html .= $catalog_toc;
					//$pdf->writeHTML($catalog_toc, true, false, true, false, '');
					//echo $catalog_toc;
				}	
			}
			
			$pdf_content = "";
			if(!empty($pdf_data))
			{
				foreach($pdf_data as $key => $category)
				{
					if(!empty($category['products']))
					{
						
						$tmp_category = str_replace("{::category_name}", $category['name'], $html_template_category);
					
						$tmp_products = "";
						$no_of_item = 0;
						foreach($category['products'] as $key2 => $product)
						{
							if ($product['image']) 
							{
								$image = $product['image'];
							} 
							else 
							{
								$image = 'no_image.jpg';
							}	
									
							$thumb = $this->model_tool_image->resize($image, $image_width, $image_height);
							$thumb = str_replace(HTTP_SERVER, "", $thumb);
							$tmp_product = str_replace("{::product_image}", $thumb, $html_template_product);
							$tmp_product = str_replace("{::txt_product_name}", $this->language->get('text_category'), $tmp_product);
							$tmp_product = str_replace("{::product_name}", $product['name'], $tmp_product);
							$tmp_product = str_replace("{::txt_prdocut_model}", $this->language->get('text_model'), $tmp_product);
							$tmp_product = str_replace("{::product_model}", $product['model'], $tmp_product);
							$tmp_product = str_replace("{::txt_product_price}", $this->language->get('text_price'), $tmp_product);
							$tmp_product = str_replace("{::product_price}", $product['price'], $tmp_product);
							
							$tmp_products .= $tmp_product;
							$no_of_item++;
							if(($no_of_item+1) > $item_per_page)
							{
								$pdf_content  .= str_replace("{::products}", $tmp_products, $tmp_category).'<div class="page_break"></div>';
								$tmp_products = "";
								$no_of_item = 0;		
							}
						}
						
						if($no_of_item > 0)
						{
							$pdf_content  .= str_replace("{::products}", $tmp_products, $tmp_category).'<div class="page_break"></div>';
							$tmp_products = "";
							$no_of_item = 0;	
						}
					}
				}
			}
			
			// output the HTML content
			//$pdf->writeHTML($pdf_content, true, false, true, false, '');
			$html .= $pdf_content;
			
			$pdf->writeHTML($html, true, false, true, false, '');
			//echo $pdf_content;die;
			// reset pointer to the last page
			$pdf->lastPage();
			
			//Close and output PDF document
			$pdf->Output('catalog.pdf', 'I');
			die;
		}
	}
?>
