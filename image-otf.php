<?php

    class OTF_Image{
        public $crop_mode = ["center", "center"];
        public $mime_type = "image/jpeg";
        public $size_name = "";
        public $quality = 70;
        public $height = 0;
        public $width = 0;

        private function is_vector_image($mime_type){ return preg_match('/image\/svg/', $mime_type); }
        private function is_image($mime_type){ return preg_match('/image/', $mime_type); }
        
        private function create_adjusted_image($image_id, $metadata){
            $original_image_path = get_attached_file($image_id);

            $image_editor = wp_get_image_editor($original_image_path);
            $image_editor->resize($this->width, $this->height, $this->crop_mode);
            $image_editor->set_quality($this->quality);
            
            $output_path = $image_editor->generate_filename(null, null, "jpg");
            $output_filename = basename($output_path);
            
            $image_editor->save($output_path, $this->mime_type);
                
            $metadata["sizes"][$this->size_name] = [
                "width" => $width, "height" => $height,
                "file" => $output_filename,
                "mime-type" => $this->mime_type,
                "quality" => $quality
            ];
            update_post_meta($image_id, "_wp_attachment_metadata", $metadata);
        }

        public function get_image_url($image_id){
            $attachment_mime_type = get_post_mime_type($image_id);
            $attachment_metadata = wp_get_attachment_metadata($image_id);

            if(!$this->is_image($attachment_mime_type)) return wp_get_attachment_url($image_id);
            else if($this->is_vector_image($attachment_mime_type)) return wp_get_attachment_url($image_id);
            else if($this->size_name == "full") return wp_get_attachment_image_url($image_id);
            else if(!empty($attachment_metadata["sizes"][$this->size_name])){
                return wp_get_attachment_image_url($image_id, $this->size_name);
            }

            try{
                $this->create_adjusted_image($image_id, $attachment_metadata);
                return wp_get_attachment_image_url($image_id, $this->size_name); 
            }
            catch(Exception $e){ return wp_get_attachment_image_url($image_id, "full"); }
        }
    }

    function get_otf_image_url(
        $image_id, $size_name, $width = 0, $height = 0, 
        $crop_mode = ["center", "center"], $mime_type = "image/jpeg", $quality = 70) {
        
        $image = new OTF_Image();
        $image->mime_type = $mime_type;
        $image->quality = $quality;
        $image->size_name = $size_name;
        $image->width = $width;
        $image->height = $height;
        $image->crop_mode = $crop_mode;

        return $image->get_image_url($image_id);
    }