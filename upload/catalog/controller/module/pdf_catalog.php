<?php  
class ControllerModulePdfcatalog extends Controller {
	protected function index() {
		$this->language->load('module/pdf_catalog');	
		
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_select'] = $this->language->get('text_select');
		$this->data['text_all_categories'] = $this->language->get('text_all_categories');
		
		$this->load->model('catalog/manufacturer');
		$this->load->model('catalog/pdf_catalog');
		$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/pdf_catalog.css');
                
	//$this->load->model('tool/seo_url'); 
		 
		//$this->data['pdf_catalog_href'] = $this->model_tool_seo_url->rewrite(HTTP_SERVER . 'index.php?route=product/pdf_catalog&category_id=');
		$this->data['pdf_catalog_href'] = HTTP_SERVER . 'index.php?route=product/pdf_catalog&category_id=';
		
		if($this->config->get('pdf_catalog_display_categories') == 1){
                    
		if($this->config->get('pdf_catalog_display_subcategories') == 0){
                        $categories = $this->model_catalog_pdf_catalog->getMaincategories();
                }else{
			$categories = $this->model_catalog_pdf_catalog->getCategories(0);
                }
		}else{
		$categories = null;
		}
		$this->data['categories']= $categories;
		
		
		$this->id = 'pdf_catalog';
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/pdf_catalog.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/pdf_catalog.tpl';
		} else {
			$this->template = 'default/template/module/pdf_catalog.tpl';
		}
		//var_dump($this);
		$this->render(); 
	}
}
?>
