<?php
/*
	Section: PL Docs
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: PageLines.com Docs
	Class Name: PLDocs

*/

class PLDocs extends PageLinesSection {

	function section_scripts() {

		wp_enqueue_script( 'pl-chosen' );
		wp_enqueue_style( 'pl-chosen' );
		pagelines_add_bodyclass( 'prettify-on' );
		wp_enqueue_script( 'prettify', PL_JS . '/prettify/prettify.min.js' );
		wp_enqueue_style( 'prettify', PL_JS . '/prettify/prettify.css' );
		add_action( 'wp_head', array( $this, 'head' ), 14 );
		wp_enqueue_script( 'stickysidebar', $this->base_url.'/stickysidebar.js', array( 'jquery' ), pl_get_cache_key(), true );
		wp_enqueue_script( 'pl-pldocs', $this->base_url.'/pldocs.js', array( 'jquery', 'stickysidebar' ), pl_get_cache_key(), true );
		wp_enqueue_script( 'pl-visual-nav', $this->base_url.'/visualnav.min.js', array( 'jquery' ), pl_get_cache_key(), true );
	}

	function section_persistent() {
		if( isset( $_GET['changelog'] ) && is_numeric( $_GET['changelog'] ) ) {
			$this->render_changelog( $_GET['changelog'] );
		}

		add_action( 'add_meta_boxes', array( $this, 'add_article_checkbox' ) );
		add_action( 'save_post', array( $this, 'article_checkbox_save' ) );
		add_action( 'add_meta_boxes', array( $this, 'product_id_textbox' ) );
		add_action( 'save_post', array( $this, 'product_id_textbox_save' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_css_box' ) );
		add_action( 'save_post', array( $this, 'css_box_save' ) );
		add_filter( 'wpseo_title', array( $this, 'change_wpseo_title' ) );
		add_action( 'pre_get_posts', array( $this, 'archive_orderby' ) );
	}

	function head() {
		global $post;
		echo pl_js_wrap("prettyPrint()");
		$css = get_post_meta( $post->ID, 'article_css', true );
		if( '' != $css ) {
			printf( "\n<style>\n%s\n</style>\n", $css );
		}
	}

	function archive_orderby($query) {

    if( $query->is_main_query() && is_post_type_archive('pldocs') ) {
	    	$query->set( 'orderby', 'name' );
	    	$query->set( 'order', 'ASC' );
    }
}



	function change_wpseo_title($title) {
		global $post;
		if( 'pldocs' !== get_post_type() )
			return $title;

		if( is_archive() ) {
			return 'PageLines Documentation';
		} elseif( is_single() ) {
			return sprintf( 'PageLines Documentation - %s', $post->post_title );
		}
		return $title;
	}

	function render_changelog( $id ) {
		@header("Content-Type: text/plain");

		$changelog = get_post_meta( $id, '_pl_woo_product_logs', true );
		echo $changelog;
		exit();
	}

	function section_head() {

		if( is_single() ) {
			echo "<script>jQuery(document).ready(function() { jQuery('.pldocs-search').slideToggle() })</script>\n";
		}
	}


	/**
	 * The main docs portal page.
	 *
	 */
	function docs_front_page() {
		?>
		<div class="pldocs-main row">


		<?php echo $this->render_search(); ?>

		<div class="pldocs-selects">

		<?php echo pl_grid_tool( 'row_start', 2, 1, 2 ); ?>
			<div class="<?php echo pl_grid_tool( 'item_class', 6 ); ?> themes">
				<h2>Themes</h2>
			<p>Select a theme below to view the documentation:<br />
			<?php echo $this->get_products_dropdown( 'themes', false, 'Select A Theme...' ); ?>
			</p>
		</div>
		<?php echo pl_grid_tool( 'row_end', 2, 1, 2 ); ?>

		<?php echo pl_grid_tool( 'row_start', 2, 2, 2 ); ?>
			<div class="<?php echo pl_grid_tool( 'item_class', 6 ); ?> extensions">
				<h2>Extensions</h2>
			<p>Select an extension below to view the documentation:<br />
			<?php echo $this->get_products_dropdown( false, 'themes', 'Select An Extension...' ); ?>
			</p>
		</div>
		<?php echo pl_grid_tool( 'row_end', 2, 2, 2 ); ?>
		<?php

		echo '</div></div></div>';
	}

	// draw the search block
	function render_search() {

		ob_start();
		?>
		<div class="pldocs-search-wrap">
			<div class="pldocs-search">
				<div class="fields">
					<form role="search" method="get" id="searchform" action="<?php bloginfo('url'); ?>" class="pldocs-form">
						<input type="text" value="" name="s" id="s" placeholder="Search The Documentation" class="docs-search-box"/>
						<select class="pldoc-dropdowns-search" name="category_name">
							<option value="">All Documentation</option>
							<option value="themes">Themes</option>
							<option value="plugins">Plugins</option>
							<option value="sections">Sections</option>
						</select>
						<input type="submit" id="searchsubmit" value="Search" class="btn btn-mini docs-search-button" />
						<input type="hidden" name="post_type" value="pldocs" />
					</form>
				</div>
			</div>
		<div class="pldocs-show-search-wrap">
			<a href="#" class="pldocs-show-search">Search <i class="icon icon-search-plus"></i></a>
		</div>
	</div>
		<?php
		return ob_get_clean();
	}


	function section_template() {

		// intercept search results...
		if( is_search() ) {
			$this->search_results();
			return false;
		}

		// if we are a category, show category stuff...
		if( is_category() ) {
			$this->archive_grid();
			return false;
		}

		// do we show portal page?
		if( is_archive() ) {
			$this->docs_front_page();
			return false;
		}

		// show a doc...
		?>
		<?php echo $this->render_search(); ?>
		<div class="pldocs-wrapper row">

			<div class="pldocs-sidebar pl-border">
				<?php echo $this->breadcrumbs(); ?>
				<div class="pldocs-mobile-drop pl-contrast">Select <i class="icon icon-caret-down"></i></div>
				<ul class="standard-list theme-list-nav">
				</ul>

				<div class="pldocs-articles">
					<?php echo $this->get_article_links(); ?>
				</div>
				<?php
					global $post;
					// lets get purchase and changelog buttons.
					$product = get_post_meta( $post->ID, 'product_id_textbox', true );
					if( is_numeric( $product ) ) {
						$link = get_permalink( $product );
						$version = get_post_meta( $product, '_pl_woo_product_version', true );
						$changelog =  get_post_meta( $product, '_pl_woo_product_logs', true );
						$product_data = new WC_Product( $product );
						if( $product_data->price > 0 )
							printf( '<a target="_blank" href="%s"><button class="btn btn-mini btn-primary"><i class="icon icon-chevron-circle-right"></i> Purchase</button></a>', $link );

						if( $changelog ) {
							printf( '<a target="_blank" href="%s"><button class="btn btn-mini"><i class="icon icon-list-ol"></i> Changelog</button></a>', add_query_arg( array( 'changelog' => $product ), site_url() ) );
						}
					}
				?>

			</div>
			<div class="pldocs-content hentry">
				<?php the_content(); ?>
				<?php echo do_shortcode('[post_edit]'); ?>
			</div>
		</div>
		<?php
	}

	// docs search results.
	// TODO add style n shit
	function search_results() {
		echo $this->render_search();
		?>
		<div class="pldocs-selects row">
			<?php printf( '<a href="%s">DOCS</a> / Search results for %s', site_url( 'docs' ), get_search_query() );
		if( have_posts() ) {
			while ( have_posts() ) : the_post();
				printf( '<h5><a href="%s">%s</a></h5>', get_permalink(), get_the_title() );
				the_excerpt();
			endwhile;

		} else {
			echo '<h3>These are not the droids you are looking for</h3>';
		}
		?></div><?php
	}

	// TODO add style n shit
	function archive_grid() {

		// we must be in a category here...
		global $wp_query;
		$total = $wp_query->post_count;
		$item_cols = ( $total <= 2 ) ? 6 : 4;
		$count = 1;

		?>
		<?php echo $this->render_search(); ?>
		<div class="pldocs-selects row">
			<?php printf( '<a href="%s">DOCS</a> / %s', site_url( 'docs' ), single_cat_title(false, false) ); ?>
		<h2><?php single_cat_title(false); ?></h2>
		<?php
		if( have_posts() ) {
			while ( have_posts() ) : the_post();
				echo pl_grid_tool( 'row_start', $item_cols, $count, $total );
				?><div class="<?php echo pl_grid_tool( 'item_class', $item_cols ); ?> themes">
					<?php
				printf( '<h3><a href="%s">%s</a></h3>', get_permalink(), get_the_title() );
				the_excerpt();
				echo '</div>';
				echo pl_grid_tool( 'row_end', $item_cols, $count, $total );
			$count++;
			endwhile;
		}
		?>
	</div>
	<?php
	}

	// return a list of 'articles' as <li> links.
	function get_article_links() {
		$articles = new WP_Query( "post_type=pldocs&meta_key=article_box_check&meta_value=on&order=ASC" );
		$output = '';
		if( empty( $articles->posts ) )
			return false;

		$articles = $articles->posts;
		$output = '<span class="articles-header">Useful Articles</span><ul>';
		foreach( $articles as $article ) {
			$output .= sprintf( '<li class="external"><a href="%s">%s</a></li>', get_permalink( $article->ID ), $article->post_title );
		}
		$output .= '</ul>';
		return $output;
	}

	function breadcrumbs() {
		global $post;
		$category_data = get_the_category();
		$slug = (get_option( 'category_base' )) ? get_option( 'category_base' ) : 'category';
		$category = ( isset( $category_data[0] ) ) ? sprintf( '<a href="%s/?post_type=pldocs">%s</a>', site_url( "/$slug/" . $category_data[0]->slug ), strtoupper( $category_data[0]->name ) ) : '';
		$title = ($category) ? ' / ' . $category . ' / ' . strtoupper( $post->post_title ) : ' / ' . strtoupper( $post->post_title );
		printf( '<a href="%s">DOCS</a>%s', site_url( 'docs'), $title );
	}


	function get_products_dropdown( $category = false, $not_in = false, $text = '' ) {

		if( $not_in ) {
			$idObj = get_category_by_slug( $not_in );
			$id = $idObj->term_id;
			$not_in = $id;
		} else {
			$not_in = false;
		}

		if( $category ) {
			$idObj = get_category_by_slug( $category );
			$id = $idObj->term_id;
			$category = $id;
		} else {
			$category = false;
		}

		$args = array(
    'post_status' => 'publish',
		'post_type'	=> 'pldocs',
		'category__in' => array( $category ),
		'category__not_in' => array( $not_in ),
		'orderby'	=> 'title',
		'order' => 'ASC'
		);
		if( ! $category )
			unset( $args['category__in'] );

		if( ! $not_in )
			unset( $args['category__not_in'] );

		$query = new WP_Query($args);

		$posts = $query->posts;
		if( empty( $posts ) )
			return false;

		if( ! $text ) {
			$text = 'Select A Product';
		}

		printf( '<select class="pldoc-dropdowns"><option>%s</option>', $text );
		foreach( $posts as $post ) {
			if( ! get_post_meta( $post->ID, 'article_box_check', true ) )
				printf( '<option value="%s">%s</option>', get_permalink( $post->ID ), $post->post_title );
		}
		echo '</select>';
	}

	/**
	 * this is all wp-admin stuff from here...
	 */
	function add_css_box() {
		add_meta_box( 'article_css', 'Custom CSS', array( $this, 'add_css_box_func' ), 'pldocs', 'advanced', 'high' );
	}

	function add_css_box_func( $post ) {
			$text = get_post_meta( $post->ID, 'article_css', true );
			?>
			<p>
				<label for="article_css">Custom CSS for this document.</label><br />
				<textarea style="width: 700px; height: 150px;" name="article_css" id="article_css"><?php echo $text; ?></textarea>
			</p>
			<?php
	}

	function css_box_save( $post_id ){
		// Bail if we're doing an auto save
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		if ( isset( $_POST['article_css'] ) ) {
			update_post_meta( $post_id, 'article_css', $_POST['article_css'] );
		} else {
			delete_post_meta( $post_id, 'article_css' );
		}
	}

	function add_article_checkbox() {
		add_meta_box( 'article-checkbox', 'Enable Article', array( $this, 'article_checkbox_func' ), 'pldocs', 'side', 'high' );
	}

	function article_checkbox_func( $post ) {
			$values = get_post_custom( $post->ID );
			$check = isset( $values['article_box_check'] ) ? esc_attr( $values['article_box_check'][0] ) : '';
			wp_nonce_field( 'my_article_box_nonce', 'article_box_nonce' );
			?>
			<p>
					<input type="checkbox" name="article_box_check" id="article_box_check" <?php checked( $check, 'on' ); ?> />
					<label for="article_box_check">Is this doc an article?'</label>
			</p>
			<?php
	}

	function article_checkbox_save( $post_id ){
		// Bail if we're doing an auto save
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		// if our nonce isn't there, or we can't verify it, bail
		if( !isset( $_POST['article_box_nonce'] ) || !wp_verify_nonce( $_POST['article_box_nonce'], 'my_article_box_nonce' ) ) return;

		if ( isset( $_POST['article_box_check'] ) && $_POST['article_box_check'] ) {
    	add_post_meta( $post_id, 'article_box_check', 'on', true );
		} else {
    	delete_post_meta( $post_id, 'article_box_check' );
		}
	}

	// all the article post_meta stuffs
	function product_id_textbox() {
		add_meta_box( 'product_id_textbox', 'Product ID', array( $this, 'product_id_textbox_func' ), 'pldocs', 'side', 'high' );
	}
	function product_id_textbox_func( $post ) {
			$values = get_post_custom( $post->ID );
			$text = isset( $values['product_id_textbox'] ) ? esc_attr( $values['product_id_textbox'][0] ) : '';
			wp_nonce_field( 'my_product_id_textbox_nonce', 'product_id_textbox_nonce' );
			?>
			<p>
					<input type="text" name="product_id_textbox" id="product_id_textbox" value="<?php echo $text; ?>" />
					<br /><label for="product_id_textbox">Product ID</label>
			</p>
			<?php
	}

	function product_id_textbox_save( $post_id ){
		// Bail if we're doing an auto save
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		// if our nonce isn't there, or we can't verify it, bail
		if( !isset( $_POST['product_id_textbox_nonce'] ) || !wp_verify_nonce( $_POST['product_id_textbox_nonce'], 'my_product_id_textbox_nonce' ) ) return;

		if ( isset( $_POST['product_id_textbox'] ) && $_POST['product_id_textbox'] ) {
			add_post_meta( $post_id, 'product_id_textbox', $_POST['product_id_textbox'], true );
		} else {
			delete_post_meta( $post_id, 'product_id_textbox' );
		}
	}

}
