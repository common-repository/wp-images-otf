=== WP Images OTF Plugin ===

Contributors: adam_prohack
Tags: media library, images, responsive, resize, dynamic
Requires at least: 3.0
Tested up to: 4.8

== What does this plugin do? ==

This plugin generate image thumbnails only when site need it, when someone call function with needed size, 
and saves disk space when image is uploaded to media library.
You don't need to use **`add_image_size()`** function.

== How it works? ==

1. Upload image to media library
2. Change function **`wp_get_attachment_image_url()`** to **`get_otf_image_url()`** in your wordpress template
3. When function is invoke the first time, the image is dynamically adjust, create and stored
4. Page serve image in need size

== Documentation ==

```php
    function get_otf_image_url(
        $image_id, $size_name, 
        $width = 0, $height = 0, 
        $crop_mode = ["center", "center"], 
        $mime_type = "image/jpeg", $quality = 70)
```

**Parameters**

* Required:
    ***$image_id**  - attachment id
    ***$size_name** - dynamic size name
    ***$width** - image width
    ***$height** - image height
* Optional
    ***$crop_mode** - image crop mode
    ***$mime_type** - result image mime type
    ***$quality** - result image quality

**Return Value**

* Returns an url string