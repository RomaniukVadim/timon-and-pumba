=== CSS Compress ===
Tags: Compression, CSS, Optimization
Contributors: chuyskywalker

Automatically removes comments, new lines, tabs, and gzip compresses (GZIP) any CSS file called with "<?php bloginfo('stylesheet_url'); ?>" Just activating the plugin with the default Kubrick theme will reduce the CSS file from 8.6k to 1.7k.

== Installation ==

1. Download css-compress.php
2. Upload css-compress.php into your wp-content/plugins directory.
3. Activate the plugin through the WordPress admin interface.

== Usage / Caveats ==

The plugin will work automatically - you don't need to do any extra work.

It has been noted though, relatively referenced urls inside the style sheet may be problematic. If nedd be, the plugin will attempt to re-write all of the url() paths to a fully qualified path - this may not work right. If things break, please file a bug with the address of the blog and contact me via AIM chuyskywlk so that I can fix this. 

== Screenshots ==

1. A screen shot of the file sizes comparing Kubrick and Compressed-Kubrick
