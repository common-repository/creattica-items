<?php

/*
Plugin Name: Creattica Items
Plugin URI: 
Description: Retrieves items from Creattica via RSS, then displays the results as a list of linked images.
Version: 1.0.0
Author: Derek Herman
Author URI: http://valendesigns.com
*/

/*  Copyright 2010  Derek Herman  (email : derek@valendesigns.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
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
 * Creattica SimplePie
 *
 * @param int $items
 * @param string $url
 * @param bool $echo
 * @return string
 */
function creattica_pie($items = '', $url = '', $echo = true) {
  if(function_exists('fetch_feed')) {
  	$feed = fetch_feed($url); // specify the source feed
  	$feed->set_cache_duration(600); // specify the cache duration in seconds
  	$limit = $feed->get_item_quantity($items); // specify number of items
  	$items = $feed->get_items(0, $limit); // create an array of items
  	$content .= '<ul class="creattica_items">';
    if ($limit == 0) {
      $content .= '<li>The Creattica feed is unavailable, sorry for the trouble.</li>';
    } else {
      foreach ($items as $item) {
        $feedDescription = $item->get_description();
        $image = creattica_returnImage($feedDescription);
        $image = creattica_scrapeImage($image);
      	$content .= '<li><a href="' . $item->get_permalink() . '" rel="external nofollow"><img src="'.$image.'" alt="' . $item->get_title() . '" class="creattica_item" /></a></li>';
      } 
    }
    $content .= '</ul>';
    if ($echo) {
      echo $content;
    } else {
      return $content;
    }
  }
}

/* 
 * Finds the first image path in a block of HTML
 * Used with SimplePie
 *
 * @param string $text
 * @return string
 */
function creattica_returnImage($text) {
  $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
  $pattern = "/<img[^>]+\>/i";
  preg_match($pattern, $text, $matches);
  $text = $matches[0];
  return $text;
}

/* 
 * Formats the returnImage() function
 * Used with SimplePie
 *
 * @param string $text
 * @return string
 */
function creattica_scrapeImage($text) {
  $pattern = '/src=[\'"]?([^\'" >]+)[\'" >]/'; 
  preg_match($pattern, $text, $link);
  $link = $link[1];
  return $link;
}

/* 
 * Creattica Images Widget
 */
if (class_exists('WP_Widget')) 
{
  class Creattica_Images_Widget extends WP_Widget 
  {
    
    function Creattica_Images_Widget() 
    {
      $widget_ops = array('classname' => 'creattica_images_widget', 'description' => 'A Gallery of Creattica Images' );
  		$this->WP_Widget('creattica_images_widget', 'Creattica Images', $widget_ops);
    }
    
    function widget($args, $instance) 
    {
  		extract($args, EXTR_SKIP);
   
  		echo $before_widget;
  		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
  		$items = empty($instance['items']) ? '1' : apply_filters('widget_items', $instance['items']);
  		$url = empty($instance['url']) ? 'http://feeds.feedburner.com/Faveup-LatestDesigns' : apply_filters('widget_url', $instance['url']);
   
  		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
  		creattica_pie($items, $url);
  		echo $after_widget;
  	}
    
    function update($new_instance, $old_instance) 
    {
      $instance = $old_instance;
      $instance['title']  = strip_tags($new_instance['title']);
  		$instance['items']  = (int) $new_instance['items'];
  		$instance['url']    = strip_tags($new_instance['url']);
   
  		return $instance;
    }
    
    function form($instance) 
    {
  		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'items' => '', 'url' => '' ) );
  		$title = strip_tags($instance['title']);
  		$items = (int) $instance['items'];
  		$url = strip_tags($instance['url']);
      ?>	
  		<p>
  		  <label for="<?php echo $this->get_field_id('title'); ?>">Title: 
  		    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
  		  </label>
  		</p>
  		<p>
  		  <label for="<?php echo $this->get_field_id('items'); ?>">Number of Items - limit 30: 
  		    <input id="<?php echo $this->get_field_id('items'); ?>" name="<?php echo $this->get_field_name('items'); ?>" type="text" value="<?php echo attribute_escape($items); ?>" size="3" />
  		  </label>
  		</p>
  		<p>
  		  <label for="<?php echo $this->get_field_id('url'); ?>">RSS Feed URL: 
  		    <input class="widefat" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo attribute_escape($url); ?>" />
  		    <small style="margin-top:5px;display:block">Visit <a href="http://creattica.com" target="_blank">Creattica</a> to get the available feeds.</small>
  		  </label>
  		</p>
      <?php
  	}
    
  }
  add_action('widgets_init', create_function('', 'return register_widget("Creattica_Images_Widget");'));
}