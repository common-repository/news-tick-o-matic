<?php
/*
Plugin Name: News-Tick-O-Matic
Plugin URI: http://plugins.twinpictures.de/plugins/news-tick-o-matic/
Description: Animated news ticker&mdash;display the newest news a smoothly scrolling sidebar.
Version: 0.2
Author: Twinpictures
Author URI: http://www.twinpictures.de
License: GPL2
*/

/*  Copyright 2012 Twinpictures (www.twinpictures.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//widgit scripts
function tickOmaticInit(){
	wp_enqueue_script('jquery');
	
	$plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );
	if (!is_admin()){
			//collapse script
			wp_register_script('jcarousellite', $plugin_url.'/js/jcarousellite_1.0.1.min.js', array ('jquery'), '1.0.1' );
			wp_enqueue_script('jcarousellite');
		
			//css
			wp_register_style( 'tickomatic-css', $plugin_url.'/css/style.css', array (), '1.0' );    
			wp_enqueue_style( 'tickomatic-css' );
	}
}
add_action( 'init', 'tickOmaticInit' );

//Word chopper
function wordChop($string, $word_limit){
	$words = explode(' ', $string, ($word_limit + 1));
	if(count($words) > $word_limit){
		array_pop($words);
		return implode(' ', $words).'...';
	}
	else{
		return implode(' ', $words);
	}
}

class NewsTickOMatic extends WP_Widget {
    /** constructor */
	function NewsTickOMatic() {
		$widget_ops = array('classname' => 'NewsTickOMatic', 'description' => __('An animated jQuery news ticker by Twinpictures') );
		$this->WP_Widget('NewsTickOMatic', 'News Ticker-O-Matic', $widget_ops);
    }
	
    /** @see WP_Widget::widget */
    function widget($args, $instance) {
		global $news_scripts;
        extract( $args );
		$id = $args['widget_id'];
        $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$post_type = empty($instance['post_type']) ? 'post' : apply_filters('widget_post_type', $instance['post_type']);
		$category = empty($instance['category']) ? '' : apply_filters('widget_category', $instance['category']);
		$catids = empty($instance['catids']) ? '' : apply_filters('widget_catids', $instance['catids']);
		$count = empty($instance['count']) ? -1 : apply_filters('widget_count', $instance['count']);
		$wordcount = empty($instance['wordcount']) ? 0 : apply_filters('widget_wordcount', $instance['wordcount']);
		$visible = empty($instance['visible']) ? 3 : apply_filters('widget_visible', $instance['visible']);
		$auto = empty($instance['auto']) ? 5 : apply_filters('widget_auto', $instance['auto']);
		$speed = empty($instance['speed']) ? 1 : apply_filters('widget_speed', $instance['speed']);
        ?>
		
		
            <?php
				echo $before_widget;
				if($title){
					echo $before_title . $title . $after_title;
				}
			?>
			<div class="newsbox">		
				<div id="<?php echo $id; ?>-ticker" class="latestnews">
					<ul class="news">
					<?php
						$args = array(
							'post_type' => explode(',',$post_type),
							'posts_per_page' => $count	
						);
						if(isset($catids) && $catids){
							$args = array(
								'post_type' => explode(',',$post_type),
								'cat' => $catids,
								'posts_per_page' => $count
							);
						}
						else if($category){
							$args = array(
								'post_type' => explode(',',$post_type),
								'category_name' => $category,
								'posts_per_page' => $count
							);
						}
						$news = new WP_Query($args);
					?>
									
					<?php if ( $news->have_posts() ) while ( $news->have_posts() ) : $news->the_post(); ?>
						<li>
							<span class="date">
							<?php
								the_time('j. F Y');
								$category = get_the_category();
								if($category[0]->cat_name){
									echo ' - '.$category[0]->cat_name;
								}
							?></span><br />
							<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent Link to');?> <?php the_title_attribute(); ?>">
								<?php
									the_title();
									$myExcerpt = get_the_excerpt();
									if ($myExcerpt != '') {
										if($wordcount > 0){
											$myExcerpt = wordChop($myExcerpt,$wordcount);
										}
										echo '</br>'.$myExcerpt;
									}
								?>
							</a>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
					</ul>
				</div><!-- end of latestnews -->
			</div><!-- end of newsbox -->					
        <?php echo $after_widget; ?>
		
		<?php
		//add the script
		$news_scripts[$id] = array(
				'id' => $id.'-ticker',
				'visible' => $visible,
				'auto' => $auto * 1000,
				'speed' => $speed * 1000
		);
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
		$instance = array_merge($old_instance, $new_instance);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = esc_attr($instance['title']);
		$post_type = esc_attr($instance['post_type']);
		$category = esc_attr($instance['category']);
		$catids = esc_attr($instance['catids']);
		$count = esc_attr($instance['count']);
		if(!$count){
			$count = -1;
		}
		$wordcount = esc_attr($instance['wordcount']);
		if(!$wordcount){
			$wordcount = 0;
		}
		$visible = esc_attr($instance['visible']);
		if(!$visible){
			$visible = 3;
		}
		$auto = esc_attr($instance['auto']);
		if(!$auto){
			$auto = 5;
		}
		$speed = esc_attr($instance['speed']);
		if(!$speed){
			$speed = 1;
		}
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
		
		<p><label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post types:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>" type="text" value="<?php echo $post_type; ?>" /></label></p>
		
        <p><label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category Name for news:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" type="text" value="<?php echo $category; ?>" /></label></p>
	    
	    <p><label for="<?php echo $this->get_field_id('catids'); ?>"><?php _e('OR Category IDs for news:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('catids'); ?>" name="<?php echo $this->get_field_name('catids'); ?>" type="text" value="<?php echo $catids; ?>" /></label></p>
	    
	    <p><label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('How many posts to grab:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo $count; ?>" /></label></p>
        
		<p><label for="<?php echo $this->get_field_id('wordcount'); ?>"><?php _e('Trim excerpt word count to:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('wordcount'); ?>" name="<?php echo $this->get_field_name('wordcount'); ?>" type="text" value="<?php echo $wordcount; ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('visible'); ?>"><?php _e('How many posts to display:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('visible'); ?>" name="<?php echo $this->get_field_name('visible'); ?>" type="text" value="<?php echo $visible; ?>" /></label></p>
		
		<p><label for="<?php echo $this->get_field_id('auto'); ?>"><?php _e('Seconds to pause on news item:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('auto'); ?>" name="<?php echo $this->get_field_name('auto'); ?>" type="text" value="<?php echo $auto; ?>" /></label></p>
		
		<p><label for="<?php echo $this->get_field_id('speed'); ?>"><?php _e('Seconds for scroll animation:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('speed'); ?>" name="<?php echo $this->get_field_name('speed'); ?>" type="text" value="<?php echo $speed; ?>" /></label></p>
	<?php 
    }

} // class Ticker Widget

// register Ticker widget
add_action('widgets_init', create_function('', 'return register_widget("NewsTickOMatic");'));

//code fore the footer
add_action('wp_footer', 'print_news_scripts');
 
function print_news_scripts() {
	global $news_scripts;
 
	if ( ! $news_scripts ){
		return;
	}
	
	?>
	<script language="javascript" type="text/javascript">
		jQuery(document).ready(function() {
			<?php			
			foreach((array) $news_scripts as $script){
				?>
				jQuery('#<?php echo $script['id']; ?>').jCarouselLite({
						vertical: true,
						hoverPause: true,
						visible: <?php echo $script['visible']; ?>,
						auto: <?php echo $script['auto']; ?>,
						speed: <?php echo $script['speed']; ?>
						
				});
				jQuery('#<?php echo $script['id']; ?> li:nth-child(odd)').addClass('alternate');
				<?php
			}
			?>
		});
	</script>
	<?php
}

?>