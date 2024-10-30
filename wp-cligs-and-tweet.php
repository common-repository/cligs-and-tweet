<?php
/*
Plugin Name: Cli.gs and Tweet
Plugin URI: http://blog.bokhorst.biz/2354/computers-en-internet/wordpress-plugin-cli-gs-and-tweet/
Description: This simple to use plugin automatically creates a Cli.gs short URL and sends a customizable Twitter message when saving a post.
Version: 0.6
Author: Marcel Bokhorst
Author URI: http://blog.bokhorst.biz/about/
*/

/*
	Copyright 2009  Marcel Bokhorst

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
	Acknowledgments

	XML Parser Class by *Adam A. Flynn*
	This class is published under the GNU Lesser General Public License version 2

	jQuery JavaScript Library
	This library is published under both the GNU General Public License and MIT License

	All licenses are GPL compatible (see http://www.gnu.org/philosophy/license-list.html#GPLCompatibleLicenses)
*/

#error_reporting(E_ALL);

// Include otp class
require_once('wp-cligs-and-tweet-class.php');

// Check pre-requisites
WPCligsAndTweet::cltw_check_prerequisites();

// Start plugin
global $wp_cligs_and_tweet;
$wp_cligs_and_tweet = new WPCligsAndTweet();

// That's it!

?>
