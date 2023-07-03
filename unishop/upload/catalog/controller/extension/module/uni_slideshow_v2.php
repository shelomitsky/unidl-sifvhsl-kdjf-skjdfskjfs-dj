<?php
class ControllerExtensionModuleUniSlideshowV2 extends Controller {
	public function index($setting) {
		static $module = 0;
		
		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		
		$this->load->model('tool/image');
		
		$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/slideshow_v2.css');
		
		$data['effect_in'] = $setting['effect_in'];
		$data['effect_out'] = $setting['effect_out'];
		$data['delay'] = $setting['delay'];
		$data['max_height'] = $setting['max_height_desktop'];
		$data['max_height_mobile'] = $setting['max_height_mobile'];
		$data['hide_on_mobile'] = isset($setting['hide']) ? true : false;

		$data['slides'] = [];
		
		$results = $setting['slides'] ? $setting['slides'] : [];
		
		$max_h = $setting['max_height_desktop'];
		$max_h_mobile = $setting['max_height_mobile'];
		
		if(!isset($setting['hide']) || (isset($setting['hide']) && !$uniset['is_mobile'])) {
			foreach ($results as $key => $result) {
			
				$original_image = isset($result['image'][$lang_id]) && $result['image'][$lang_id] != '' ? $result['image'][$lang_id] : '';
			
				if($original_image && is_file(DIR_IMAGE . $original_image)) {
				
					$size = getimagesize(DIR_IMAGE . $original_image);
	
					$width = $size[0];
					$height = $size[1];
					
					$new_w = ceil($width/($height/$max_h));
					
					if(($max_h < $height) && $new_w) {
						$width = $new_w;
						$height = $max_h;
					}
				
					$image = $this->model_tool_image->resize($original_image, $width, $height);
				
					$original_image_mobile = $result['image_mobile'][$lang_id];

					if($original_image_mobile && is_file(DIR_IMAGE . $original_image_mobile)) {
						$size_mobile = getimagesize(DIR_IMAGE . $original_image_mobile);
					
						$width_mobile = $size_mobile[0];
						$height_mobile = $size_mobile[1];
					
						$new_w_mobile = ceil($width_mobile/($height_mobile/$max_h_mobile));
					
						if(($max_h_mobile < $height_mobile) && $new_w_mobile) {
							$width_mobile = $new_w_mobile;
							$height_mobile = $max_h_mobile;
						}
					
						$image_mobile = $this->model_tool_image->resize($original_image_mobile, $width_mobile, $height_mobile);
					} else {
						$image_mobile = '';
					}
				
					$title = isset($result['title'][$lang_id]) ? $result['title'][$lang_id] : '';
					$text = isset($result['text'][$lang_id]) ? $result['text'][$lang_id] : '';
					$button = isset($result['button'][$lang_id]) ? $result['button'][$lang_id] : '';
				
					$has_text = ($title && $text) ? true : false;
					$text_over_image = isset($result['text_over_image'][$lang_id]) ? $result['text_over_image'][$lang_id] : !$has_text;
					$sort = isset($result['sort'][$lang_id]) ? $result['sort'][$lang_id] : 0;
				
					$data['slides'][] = [
						'image' 			=> $image,
						'image_mobile' 		=> $image_mobile,
						'width' 			=> $width,
						'height' 			=> $height,
						'title' 			=> $title,
						'text' 				=> $text,
						'link' 	 			=> $result['link'][$lang_id],
						'button' 			=> $button,
						'text_over_image'	=> $text_over_image,
						'has_text'			=> $has_text,
						'text_hide_mobile'	=> isset($result['text_hide_mobile']) ? $result['text_hide_mobile'][$lang_id] : false,
						'sort'				=> $sort
					];
				}
			}
		
			if(count($data['slides']) > 1) { 
				array_multisort(array_column($data['slides'], 'sort'), SORT_ASC, $data['slides']);
			}

			$data['module'] = $module++;
		}

		return $this->load->view('extension/module/uni_slideshow_v2', $data);
	}
}