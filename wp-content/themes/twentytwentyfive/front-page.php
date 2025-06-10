<?php
/**
 * Front Page Template showing Bloom static homepage.
 */
$theme_dir  = get_stylesheet_directory();
$theme_uri  = get_stylesheet_directory_uri();
$html = file_get_contents($theme_dir . '/bloom/index.html');
// Prefix asset paths
$html = str_replace('href="assets/', 'href="' . $theme_uri . '/bloom/assets/', $html);
$html = str_replace('src="assets/', 'src="' . $theme_uri . '/bloom/assets/', $html);
$html = str_replace("url('assets/", "url('" . $theme_uri . '/bloom/assets/', $html);
$html = str_replace('url("assets/', 'url("' . $theme_uri . '/bloom/assets/', $html);

echo $html;

