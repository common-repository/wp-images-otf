<?php
    /*
        *************************************************************************

        Plugin Name:  WP Images OTF Plugin
        Plugin URI:   https://github.com/adam-prohack/WP-Images-OTF-Plugin
        Description:  This plugin allow you to create attachment image in custom size, only when you need this size
        Version:      1.0.0
        Author:       Adam Brzozowski
        Text Domain:  wp-images-otf

        Copyright (C) 2017 Adam Brzozowski

        *************************************************************************
    */

    load_plugin_textdomain("wp-images-otf");

    require_once(__DIR__."/admin-panel.php");
    require_once(__DIR__."/image-otf.php");