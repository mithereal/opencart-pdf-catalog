<?php

class ControllerProductPdfcatalog extends Controller {

    public function index() {
        $this->load->config('pdf_catalog');
        $this->load->model('catalog/pdf_catalog');
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

        if (!isset($this->request->get['category_id']) || $this->request->get['category_id'] == "0") {
            $categories = $this->model_catalog_pdf_catalog->getCategories(0);

            foreach ($categories as $key => $category) {
                $products = $this->model_catalog_pdf_catalog->getProductsByCategoryId($category['category_id'], $data);
                if (!empty($products)) {
                    foreach ($products as $key2 => $product) {
                        $products[$key2]['price'] = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
                        if ($this->config->get('pdf_catalog_description') && strlen(trim($this->config->get('pdf_catalog_description'))) > 1) {
                            $products[$key2]['description'] = $product['description'];
                        }
                    }
                }
                $categories[$key]['products'] = $products;
            }

            $this->createPdf($categories);
        } elseif ($this->request->get['category_id'] != "0") {
            $category = $this->model_catalog_pdf_catalog->getCategory($this->request->get['category_id']);
            $categories = $this->model_catalog_pdf_catalog->getCategories($this->request->get['category_id']);


            if (count($categories) > 0) {

                $data = array(
                    'status' => 1,
                    'sort' => 'pd.name',
                    'limit' => $limit
                );
                
                if($this->config->get('pdf_catalog_display_disabled') == 0){
                    $data['filter_status'] = 1;
                }
                if($this->config->get('pdf_catalog_display_out_of_stock') == 0){
                    $data['filter_quantity'] = 1;
                }

                if (isset($category['category_id'])) {
                    $products = $this->model_catalog_pdf_catalog->getProductsByCategoryId($category['category_id'], $data);

                    if (!empty($products)) {
                        foreach ($products as $key2 => $product) {
                            $products[$key2]['price'] = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
                            if ($this->config->get('pdf_catalog_display_description') == "1")
                                $products[$key2]['description'] = $product['description'];
                        }
                    }
                    $category['products'] = $products;
                    $main_category = array($category);
                }

                foreach ($categories as $key => $category) {
                    $products = $this->model_catalog_pdf_catalog->getProductsByCategoryId($category['category_id'], $data);
                    if (!empty($products)) {
                        foreach ($products as $key2 => $product) {
                            $products[$key2]['price'] = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
                            if ($this->config->get('pdf_catalog_description') && strlen(trim($this->config->get('pdf_catalog_description'))) > 1) {
                                $products[$key2]['description'] = $product['description'];
                            }
                        }
                    }
                    $categories[$key]['products'] = $products;
                }

                $categories = array_merge($main_category, $categories);



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
            } else {
                if (isset($category['category_id'])) {
                    $products = $this->model_catalog_pdf_catalog->getProductsByCategoryId($category['category_id'], $data);

                    if (!empty($products)) {
                        foreach ($products as $key2 => $product) {
                            $products[$key2]['price'] = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
                            if ($this->config->get('pdf_catalog_display_description') == "1")
                                $products[$key2]['description'] = $product['description'];
                        }
                    }
                    $category['products'] = $products;
                }
                $this->createPdf(array(0 => $category));
            }
        }
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

    public function native_template($pdf, $pdf_data) {
        $margins = array(1, 1, 1, 1);
        $padding = array(1, 1, 1, 1);
        $bgcolor = array(255, 255, 127);
        $products = $pdf_data;
        $pdf = $this->simpletable($pdf, $products, $margins, $padding, $bgcolor);
        return $pdf;
    }

    public function simpletable($pdf = null, $data = null, $margins, $padding, $bgcolor) {
             $pdf->setCellPaddings($padding[0], $padding[1], $padding[2],$padding[3]);
             $pdf->setCellMargins($margins[0], $margins[1], $margins[2],$margins[3]);
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
                        $description = strip_tags($product['description']);
                        $description = html_entity_decode($description);
                        $description = strip_tags($description);
                        $description = trim($description);

                        $description = $this->truncate($description, $this->config->get('pdf_catalog_description_chars'));

                        $thumb = $this->model_tool_image->resize($image, $image_width, $image_height);
                        $thumb = str_replace(HTTP_SERVER, "", $thumb);
                        $tmp_product = str_replace("{::product_image}", $thumb, $html_template_product);
                        $tmp_product = str_replace("{::txt_product_name}", $this->language->get('text_category'), $tmp_product);
                        $tmp_product = str_replace("{::product_name}", $product['name'], $tmp_product);
                        $tmp_product = str_replace("{::txt_prdocut_model}", $this->language->get('text_model'), $tmp_product);
                        $tmp_product = str_replace("{::product_model}", $product['model'], $tmp_product);
                        $tmp_product = str_replace("{::txt_product_price}", $this->language->get('text_price'), $tmp_product);

                        if ($this->config->get('pdf_catalog_display_description') == "1") {
                            $tmp_product = str_replace("{::txt_product_description}", $this->language->get('text_description'), $tmp_product);
                        } else {
                            $tmp_product = str_replace("{::txt_product_description}", '', $tmp_product);
                        }

                        if ($this->config->get('pdf_catalog_display_description') == "1") {

                            $tmp_product = str_replace("{::product_description}", $description, $tmp_product);
                        } else {
                            $tmp_product = str_replace("{::product_description}", '', $tmp_product);
                        }

                        $tmp_product = str_replace("{::product_price}", $product['price'], $tmp_product);

                        $tmp_products .= $tmp_product;
                        $no_of_item++;
                        if (($no_of_item + 1) > $item_per_page) {
                            $pdf_content .= str_replace("{::products}", $tmp_products, $tmp_category) . '<div class="page_break"></div>';
                            $tmp_products = "";
                            $no_of_item = 0;
                        }
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

        $pdf->writeHTML($html, true, false, true, false, '');
        return $pdf;
    }

    public function truncate($string, $lines) {
        if ($lines > 1) {
            $parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
            $parts_count = count($parts);

            $length = 0;
	    $end = '';
            $last_part = 0;
            for (; $last_part < $parts_count; ++$last_part) {
                $length += strlen($parts[$last_part]);
                if ($length > $lines) {
		    $end = ' ...';
                    break;
                }
            }
            $string = implode(array_slice($parts, 0, $last_part)) . $end;
        }
        return $string;
    }

}
