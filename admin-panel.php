<?php

    class OTF_Panel{
        private $protected_sizes = [
            "thumb", "thumbnail",
            "large", "medium_large",
            "medium", "post-thumbnail"
        ];
        private $images_sizes = [];
        private $images_count = [];
        private $images_per_action = 50;

        private function get_images_sizes(){
            $images_data = get_posts(["numberposts" => -1, "post_type" => "attachment","post_mime_type" => "image"]);
            $images_sizes = [];
            $this->images_count = count($images_data);
            foreach($images_data as $image){
                $meta_data = wp_get_attachment_metadata($image->ID);
                if(empty($meta_data["sizes"])) continue;
                foreach($meta_data["sizes"] as $name => $size){
                    if(empty($images_sizes[$name])) $images_sizes[$name] = 0;
                    $images_sizes[$name]++;
                }
            }
            asort($images_sizes);
            return $images_sizes;
        }

        public function page_render(){
            if(!current_user_can('manage_options')) wp_die(__("Access denied.", "wp-images-otf"));
            ?>
            <div class="wrap">
                <h1>Image sizes:</h1>
                <br />
                <table>
                    <thead>
                        <th>Name</th>
                        <th>Count</th>
                    </thead>
                    <tbody>
                        <?php foreach($this->get_images_sizes() as $name => $count): ?>
                        <tr>
                            <td><?php echo $name?></td>
                            <td><?php echo $count?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <h2>Clean up images:</h2>
                <p>Remove images in additional sizes</p>
                <button id="otf_clean_up_images">Clean up</button>
                <progress id="otf_progressbar" value="0"></progress>
            </div>
            <style>
                table{ text-align: left; }
                th, td{ padding: 2px 15px 2px 0; }
                tfoot button{ margin: 10px 0; }
            </style>
            <script>
                window.addEventListener("load", () => {
                    var images_count = <?php echo $this->images_count; ?>;
                    var images_per_action = <?php echo $this->images_per_action; ?>;
                    var $ = jQuery;
                    $("#otf_clean_up_images").click(() => {
                        $("#otf_progressbar").attr("max", Math.round(images_count / images_per_action) + 1);
                        $("#otf_progressbar").attr("value", 0);
                        for(let i=0;i<Math.round(images_count / images_per_action) + 1;i++){
                            $.ajax({
                                type: "POST",
                                url: ajaxurl,
                                async: true,
                                success: () => { 
                                    $("#otf_progressbar").attr("value", $("#otf_progressbar").attr("value") + 1);
                                    if($("#otf_progressbar").attr("value") == $("#otf_progressbar").attr("max")){
                                        document.location.reload();
                                    }
                                },
                                data: {
                                    "action": "otf_clean_images",
                                    "page": i+1
                                }
                            });
                        }
                    });
                });
            </script>
            <?php
        }

        public function clean_images_action(){
            if(!current_user_can('manage_options')) wp_die(__("Access denied.", "wp-images-otf"));
            $page = filter_input(INPUT_POST, "page");
            $images_data = get_posts([
                "paged" => $page,
                "posts_per_page" => $this->images_per_action,
                "post_type" => "attachment",
                "post_mime_type" => "image"
            ]);

            foreach($images_data as $image){
                if(empty(wp_get_attachment_metadata($image->ID)["sizes"])) continue;

                $meta_data = wp_get_attachment_metadata($image->ID);
                $image_path = get_attached_file($image->ID);
                $image_path_info = pathinfo($image_path);
                $image_base_path = dirname($image_path);

                foreach($meta_data["sizes"] as $size_name => $size){
                    if(array_search($size_name, $this->protected_sizes)) continue;
                    unset($meta_data["sizes"][$size_name]);
                    unlink($image_base_path.DIRECTORY_SEPARATOR.$size["file"]);
                }
                update_post_meta($image->ID, "_wp_attachment_metadata", $meta_data);
            }
            wp_die("success");
        }

        public function add_menu_items(){
            add_submenu_page(
                "upload.php", 
                __("WP Images OTF", "wp-images-otf"), 
                __("WP Images OTF", "wp-images-otf"),
                "manage_options", "wp-images-otf", [$this, "page_render"]
            );
        }

        public function __construct(){
            add_action("admin_menu", [$this, "add_menu_items"]);
            add_action("wp_ajax_otf_clean_images", [$this, "clean_images_action"]);            
        }
    }
    new OTF_Panel();