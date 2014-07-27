<?php

class ControllerProductPdfcatalog extends Controller {

    public function index() {
        $this->load->config('pdf_catalog');
        $this->load->model('catalog/pdf_catalog');
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');
        $this->load->language('module/pdf_catalog');
		$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/pdf_catalog.css');

        $this->data['entry_position'] = $this->language->get('entry_position');
        $this->data['text_description'] = $this->language->get('text_description');

        $this->load->model('tool/image');
        $limit = $this->config->get('pdf_catalog_max_products');
        $sort = $this->config->get('pdf_catalog_sort_products');
        $data = array(
            'status' => 1,
            'sort' => 'pd.name',
           // 'sort' => $sort,
            'limit' => $limit
        );

        if($this->config->get('pdf_catalog_display_disabled') == 0){
            $data['filter_status'] = 1;
        }
        if($this->config->get('pdf_catalog_display_out_of_stock') == 0){
            $data['filter_quantity'] = 1;
        }

        if (isset($this->request->get['category_id'])) {
			$parentCategory = $this->request->get['category_id'];
		} else {
			$parentCategory = 0;
		}
		$categories = $this->model_catalog_pdf_catalog->getCategories($parentCategory);
		foreach ($categories as $key => $category) {
			$products = $this->model_catalog_pdf_catalog->getProductsByCategoryId($category['category_id'], $data);
			if (!empty($products)) {
				foreach ($products as $key2 => $product) {
					$products[$key2]['price'] = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
					$options = $this->model_catalog_product->getProductOptions($products[$key2]['product_id']);
					$attributes = $this->model_catalog_product->getProductAttributes($products[$key2]['product_id']);
					$discounts = $this->model_catalog_product->getProductDiscounts($products[$key2]['product_id']);
					$specials = $this->model_catalog_product->getProductSpecials($products[$key2]['product_id']);
					$products[$key2]['options'] = $options;
                    if($this->config->get('pdf_catalog_display_manufacturer_logo') == 1 || $this->config->get('pdf_catalog_display_manufacturer_name') == 1){
                        
                    $manufacturer = $this->model_catalog_manufacturer->getManufacturer($products[$key2]['manufacturer_id']);
                    if(isset($manufacturer['image']) && isset($manufacturer['name'])){
                        //var_dump($manufacturer);
                    $products[$key2]['manufacturer_logo_url'] = $manufacturer['image'];
                    $products[$key2]['manufacturer_name'] = $manufacturer['name'];
                    }
                    
                    }
                                        
					if ($this->config->get('pdf_catalog_description') && strlen(trim($this->config->get('pdf_catalog_description'))) > 1) {
						$products[$key2]['description'] = $product['description'];
					}

				}
			}
			$categories[$key]['products'] = $products;
		}

		$totalproducts = 0;
		foreach ($categories as $k => $categori) {
			$totalproducts = $totalproducts + count($categori['products']);
			$productsizes[$k] = count($categori['products']);
		}

		while ($totalproducts > $limit) {
			$categories = $this->removeProduct($categories, $productsizes);
			$totalproducts--;
			foreach ($categories as $k => $categori) {
				$productsizes[$k] = count($categori['products']);
			}
		}

		$this->createPdf($categories);
    }

    public function removeProduct($categories, $sizes) {

        $flipped = array_flip($sizes);
        array_multisort($flipped, SORT_DESC);

        $highest_value = $flipped[0];

        foreach ($sizes as $k => $size) {

            if ($size == $highest_value) {
                array_pop($categories[$k]['products']);
            }
        }


        return $categories;
    }

    public function createPdf($pdf_data) {
        $datacount = count($pdf_data[0]);

        if ($datacount > 1) {

            $image_width = (int) $this->config->get('pdf_catalog_image_width');
            $image_height = (int) $this->config->get('pdf_catalog_image_height');
            $item_per_page = (int) $this->config->get('pdf_catalog_item_per_page');


            $this->load->helper('tcpdf/config/tcpdf_config');
            $this->load->helper('tcpdf/tcpdf');

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $author = $this->config->get('pdf_catalog_author');
            $title = $this->config->get('pdf_catalog_title');
            $subject = $this->config->get('pdf_catalog_subject');
            $keywords = $this->config->get('pdf_catalog_description');

            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($author);
            $pdf->SetTitle($title);
            $pdf->SetSubject($subject);
            $pdf->SetKeywords($keywords);

            // set default header data
            $pdf_logo = "../../../../image/pdf_catalog_default_logo.png";

            if (file_exists($pdf_logo) == false) {
                $pdf_logo = "pdf_catalog_default_logo.png";
            }
            $pdf_title = $this->config->get('config_name');
			$pdf_string = $this->config->get('config_url');
            $pdf->SetHeaderData($pdf_logo, PDF_HEADER_LOGO_WIDTH, $pdf_title, $pdf_string);

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

            // set font
            $pdf->SetFont('helvetica', '', 10);
            
            if($this->config->get('pdf_catalog_text_orientation') == 'rtl')
            {
            $pdf->setRTL($enable = true, $resetx = true);	
            }

            // add a page
            $pdf->AddPage();

            if ($this->config->get('pdf_catalog_template_type') == 'native') {
                 $pdf = $this->native_template($pdf, $pdf_data);
             
            } else {
                 $pdf = $this->html_template($pdf, $pdf_data, $image_height, $image_width, $item_per_page);
            }

            // reset pointer to the last page and remove it
            $pdf->lastPage();
            $lastpage = $pdf->getPage();
            $pdf->deletePage($lastpage);

            //Close and output PDF document
            $pdf->Output('catalog.pdf', 'I');
            die;
        } else {
            echo "Category # " . $this->request->get['category_id'] . " Not Found";
        }
    }

    //@TODO: fix native template
    public function native_template($pdf, $pdf_data) {
        $margins = array(1, 1, 1, 1);
        $padding = array(1, 1, 1, 1);
        $bgcolor = array(255, 255, 127);
        $products = $pdf_data;
        $pdf->setCellPaddings($padding[0], $padding[1], $padding[2], $padding[3]);
        $pdf->setCellMargins($margins[0], $margins[1], $margins[2], $margins[3]);
        $pdf->SetFillColor($bgcolor[0], $bgcolor[1], $bgcolor[2]);

// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
// set some text for example
        $txt = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
        $pdf->MultiCell(100, 5, '[price] ' . $txt, 1, 'L', 1, 1, '', '', true);

//loop through products and build pdf
//        foreach($data as $prod){
//        $pdf->MultiCell(100, 5, '[price] ' .$prod[price], 1, 'L', 1, 1, '', '', true);
//        $pdf->Ln(4);
//   or
//        $pdf->MultiRow('Row '.($i), $prod[price]."\n");
//        }
        return $pdf;
    }

    public function html_template($pdf, $pdf_data, $image_height, $image_width, $item_per_page) {
		
        $html_template_css = PDF_CATALOG_TEMPLATE_CSS;
        $html_template_category = PDF_CATALOG_TEMPLATE_CATEGORY;
        $html_template_product = PDF_CATALOG_TEMPLATE_PRODUCT;

        $html_template_css = str_replace("{::image_height}", $image_height, $html_template_css);
        $html = $html_template_css;

        if ($this->config->get('pdf_catalog_description') && strlen(trim($this->config->get('pdf_catalog_description'))) > 1) {
            $html_description = '<div>' . html_entity_decode($this->config->get('pdf_catalog_description')) . '</div><div class="page_break"></div>';
            $html .= $html_description;
        }

        $no_of_category = 0;
        if ($this->config->get('pdf_catalog_display_toc') == 1 && $this->request->get['category_id'] == 0) {
            if (!empty($pdf_data)) {
                $html_catalog_toc = PDF_TOC_CATEGORY;
                $catalog_toc = PDF_TOC_TITLE;
                $catalog_toc = str_replace("{::category_title}", $this->language->get('text_category_title'), $catalog_toc);
                foreach ($pdf_data as $key => $category) {
                    $tmp_catalog_toc = str_replace("{::category_name}", $category['name'], $html_catalog_toc);
                    $tmp_catalog_toc = str_replace("{::product_cnt}", count($category['products']), $tmp_catalog_toc);
                    $catalog_toc .= $tmp_catalog_toc;
                    $no_of_category++;
                }
            }

            if ($no_of_category > 1) {
                $catalog_toc .= '<div class="page_break"></div>';
                $html .= $catalog_toc;
            }
        }

        $pdf_content = "";
        if (!empty($pdf_data)) {
            foreach ($pdf_data as $key => $category) {
                if (!empty($category['products'])) {

                    $tmp_category = str_replace("{::category_name}", $category['name'], $html_template_category);

                    $tmp_products = "";
                    $no_of_item = 0;
                    foreach ($category['products'] as $key2 => $product) {
                        if ($product['image']) {
                            $image = $product['image'];
                        } else {
                            $image = 'no_image.jpg';
                        }

                        $thumb = $this->model_tool_image->resize($image, $image_width, $image_height);
                        $thumb = str_replace(HTTP_SERVER, "", $thumb);
                        $tmp_product = str_replace("{::product_image}", $thumb, $html_template_product);
                        $tmp_product = str_replace("{::txt_product_name}", $this->language->get('text_category'), $tmp_product);
                        $tmp_product = str_replace("{::product_name}", $product['name'], $tmp_product);
                        $tmp_product = str_replace("{::txt_product_model}", $this->language->get('text_model'), $tmp_product);
                        $tmp_product = str_replace("{::product_model}", $product['model'], $tmp_product);
                        $tmp_product = str_replace("{::txt_product_price}", $this->language->get('text_price'), $tmp_product);
                        
                        $tmp_product = str_replace("{::txt_product_attributes}", $this->language->get('txt_product_attributes'), $tmp_product);
                        $tmp_product = str_replace("{::txt_product_discounts}", $this->language->get('txt_product_discounts'), $tmp_product);
                        $tmp_product = str_replace("{::txt_product_specials}", $this->language->get('txt_product_specials'), $tmp_product);

						$description = "";
                        if ($this->config->get('pdf_catalog_display_description') == "1") {
							$description = html_entity_decode($product['description']);                 
							$description = $this->fixfontsize($description);
							$description = trim($description);
							if (0>(int)$this->config->get('pdf_catalog_description_chars')) {
								$description = $this->truncate($description, $this->config->get('pdf_catalog_description_chars'), $ending = '...', $exact = false, $considerHtml = true);
							}
						}
						if (strlen($description) > 0){					
                            $tmp_product = str_replace("{::txt_product_description}", $this->language->get('text_description'), $tmp_product);
                            $tmp_product = str_replace("{::product_description}", $description, $tmp_product);
                        } else {
                            $tmp_product = str_replace("{::txt_product_description}", '', $tmp_product);
                            $tmp_product = str_replace("{::product_description}", '', $tmp_product);
                        }
                        
                        $max_options=(int)$this->config->get('pdf_catalog_max_options');
                        $max_per_options=(int)$this->config->get('pdf_catalog_max_per_options');
                         
                        if(isset($product['options']) && is_array($product['options']) && count($product['options'])>0 && $max_options > 0){
							$product_options='<ul>';
							$poc=count($product['options']);
							$poc=min($max_options, $poc);

							for($k=0; $k < $poc; $k++){
								$product_options.='<li></li>';
								if(isset($product['options'][$k]['name'])){
									$product_options.='<li><em>'.$product['options'][$k]['name'].'</em>:</li>';
								}
								if(isset($product['options'][$k]['option_value']) && is_array($product['options'][$k]['option_value']) && $max_per_options > 0 ){
									$peroc=count($product['options'][$k]['option_value']);
									$peroc=min($max_per_options, $peroc); 									
									for($i=0; $i < $peroc; $i++){                              
										$product_options.='<li>'.$product['options'][$k]['option_value'][$i]['name'];
										if ($product['options'][$k]['option_value'][$i]['price'] != "0") {
											$product_options.='    '.$product['options'][$k]['option_value'][$i]['price_prefix'].' '
											.$this->currency->format($this->tax->calculate($product['options'][$k]['option_value'][$i]['price'], $product['tax_class_id'], $this->config->get('config_tax')));
										}
										$product_options.='</li>';
									}
								}
                           
							}
							$product_options.='</ul>';
							$tmp_product = str_replace("{::product_options}", $product_options, $tmp_product);
							$tmp_product = str_replace("{::txt_product_options}", $this->language->get('text_product_options'), $tmp_product);
                        }else {
                            $tmp_product = str_replace("{::product_options}", '', $tmp_product);
                            $tmp_product = str_replace("{::txt_product_options}", '', $tmp_product);
                        }
                        
                        $tmp_product = str_replace("{::txt_manufacturer}}", '', $tmp_product);
                       
                        if ($this->config->get('pdf_catalog_display_manufacturer_logo') == 1 && isset($product['manufacturer_logo_url'])) {
                        $mlogo = $this->model_tool_image->resize($product['manufacturer_logo_url'], $image_width, $image_height);
                        $mlogo = str_replace(HTTP_SERVER, "", $mlogo);
                        $tmp_product = str_replace("{::manufacturer_logo}", $mlogo, $tmp_product);
                        
 
                        }else{
                            $tmp_product = str_replace("{::txt_manufacturer}}", '', $tmp_product);
                             $tmp_product = str_replace("{::manufacturer_logo}", '', $tmp_product);
                        }
                        
                        if ($this->config->get('pdf_catalog_display_manufacturer_name') == 1 && isset($product['manufacturer_name']) ) {
                            $tmp_product = str_replace("{::txt_manufacturer}}", '', $tmp_product);
                            $tmp_product = str_replace("{::txt_manufacturer_name}", $product['manufacturer_name'], $tmp_product);
                            
                        }else{
                            $tmp_product = str_replace("{::txt_manufacturer}}", '', $tmp_product);
                             $tmp_product = str_replace("{::txt_manufacturer_name}", '', $tmp_product);
                        }
                        
                        
                        $tmp_product = str_replace("{::product_price}", $product['price'], $tmp_product);

                        $tmp_products .= $tmp_product;
                        $no_of_item++;
                    }

                    if ($no_of_item > 0) {
                        $pdf_content .= str_replace("{::products}", $tmp_products, $tmp_category) . '<div class="page_break"></div>';
                        $tmp_products = "";
                        $no_of_item = 0;
                    }
                }
            }
        }

        $html .= $pdf_content;
        
        if($this->config->get('pdf_catalog_remove_empty_tags') == 1){
           $html = $this->remove_empty_tags_recursive($html);
        }
		
        $pdf->writeHTML($html, true, false, true, false, '');
        return $pdf;
    }
	
	function remove_empty_tags_recursive ($str, $repto = NULL)
	{
		if (!is_string ($str)|| trim ($str) == '')
				return $str;

		$start_len = 0;
		do {
			$start_len = strlen($str);
			$str = preg_replace('/<(.*?)\s*>(\s|&nbsp;)*<\/\1\s*>/ims','',$str);
		} while (strlen($str) != $start_len);
		return $str;
	}

    function fixfontsize($text) {
        $text= preg_replace ('/size=".*?"/' , '' , $text );
        $text= preg_replace ('/font-size:.*?;/' , '' , $text );
		$text= preg_replace ('/style="\s*"/' , '' , $text );
        return $text;
    }
    
    function truncate($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
	if ($considerHtml) {
		// if the plain text is shorter than the maximum length, return the whole text
		if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
			return $text;
		}
		// splits all html-tags to scanable lines
		preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
		$total_length = strlen($ending);
		$open_tags = array();
		$truncate = '';
		foreach ($lines as $line_matchings) {
			// if there is any html-tag in this line, handle it and add it (uncounted) to the output
			if (!empty($line_matchings[1])) {
				// if it's an "empty element" with or without xhtml-conform closing slash
				if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
					// do nothing
				// if tag is a closing tag
				} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
					// delete tag from $open_tags list
					$pos = array_search($tag_matchings[1], $open_tags);
					if ($pos !== false) {
					unset($open_tags[$pos]);
					}
				// if tag is an opening tag
				} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
					// add tag to the beginning of $open_tags list
					array_unshift($open_tags, strtolower($tag_matchings[1]));
				}
				// add html-tag to $truncate'd text
				$truncate .= $line_matchings[1];
			}
			// calculate the length of the plain text part of the line; handle entities as one character
			$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
			if ($total_length+$content_length> $length) {
				// the number of characters which are left
				$left = $length - $total_length;
				$entities_length = 0;
				// search for html entities
				if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
					// calculate the real length of all entities in the legal range
					foreach ($entities[0] as $entity) {
						if ($entity[1]+1-$entities_length <= $left) {
							$left--;
							$entities_length += strlen($entity[0]);
						} else {
							// no more characters left
							break;
						}
					}
				}
				$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
				// maximum lenght is reached, so get off the loop
				break;
			} else {
				$truncate .= $line_matchings[2];
				$total_length += $content_length;
			}
			// if the maximum length is reached, get off the loop
			if($total_length>= $length) {
				break;
			}
		}
	} else {
		if (strlen($text) <= $length) {
			return $text;
		} else {
			$truncate = substr($text, 0, $length - strlen($ending));
		}
	}
	// if the words shouldn't be cut in the middle...
	if (!$exact) {
		// ...search the last occurance of a space...
		$spacepos = strrpos($truncate, ' ');
		if (isset($spacepos)) {
			// ...and cut the text in this position
			$truncate = substr($truncate, 0, $spacepos);
		}
	}
	// add the defined ending to the text
	$truncate .= $ending;
	if($considerHtml) {
		// close all unclosed html-tags
		foreach ($open_tags as $tag) {
			$truncate .= '</' . $tag . '>';
		}
	}
	return $truncate;
}
 

}
