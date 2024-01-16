<?php
/**
 * Provide a dashboard bookmark category listing
 *
 * This file is used to mark up the admin-facing bookmark category listing
 *
 * @link       https://codeboxr.com
 * @since      1.0.7
 *
 * @package    cbxwpbookmark
 * @subpackage cbxwpbookmark/templates/admin
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<?php
$bookmark_category = new CBXWPBookmark_Category_Table( [ 'screen' => get_current_screen()->id ] );

//Fetch, prepare, sort, and filter CBXSCRatingReviewLog data
$bookmark_category->prepare_items();
?>
<div class="wrap cbx-chota cbxwpbookmark-page-wrapper cbxwpbookmark-category-listing-wrapper" id="cbxwpbookmark-category-listing">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2></h2>
				<?php do_action('cbxwpbookmark_wpheading_wrap_before', 'cbxwpbookmark-cats'); ?>
                <div class="wp-heading-wrap">
                    <div class="wp-heading-wrap-left pull-left">
						<?php do_action('cbxwpbookmark_wpheading_wrap_left_before', 'cbxwpbookmark-cats'); ?>
                        <h1 class="wp-heading-inline wp-heading-inline-cbxwpbookmark">
							<?php esc_html_e('Category Manager', 'cbxwpbookmark'); ?>
                            <a id="create-new-cat" href="<?php echo admin_url('admin.php?page=cbxwpbookmarkcats&view=edit&id=0'); ?>" class="button secondary ml-10"><?php esc_html_e('Create New', 'cbxwpbookmark'); ?></a>
                        </h1>
						<?php do_action('cbxwpbookmark_wpheading_wrap_left_after', 'cbxwpbookmark-cats'); ?>
                    </div>
                    <div class="wp-heading-wrap-right  pull-right">
						<?php do_action('cbxwpbookmark_wpheading_wrap_right_before', 'cbxwpbookmark-cats'); ?>
                        <a href="<?php echo admin_url('admin.php?page=cbxwpbookmark_settings'); ?>" class="button outline primary"><?php esc_html_e('Global Settings', 'cbxwpbookmark'); ?></a>
						<?php do_action('cbxwpbookmark_wpheading_wrap_right_after', 'cbxwpbookmark-cats'); ?>
                    </div>
                </div>
				<?php do_action('cbxwpbookmark_wpheading_wrap_after', 'cbxwpbookmark-cats'); ?>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12">
				<?php do_action('cbxwpbookmark_cats_listing_before_postbox'); ?>
                <div class="postbox">
                    <div class="inside">
						<?php do_action('cbxwpbookmark_cats_listing_before'); ?>
                        <form id="cbxwpbookmark_logs" method="post" class="cbx-wplisttable">
							<?php do_action('cbxwpbookmark_cats_listing_form_start'); ?>
							<?php $bookmark_category->views(); ?>
                            <input type="hidden" name="page" value="<?php echo esc_attr(wp_unslash($_REQUEST['page'])) ?>"/>
                            <div id="cbxwpbookmark_listing_filters_wrap" class="cbxwpbookmark_wplisting_filters_wrap">
								<?php $bookmark_category->search_box(esc_attr__('Search', 'cbxwpbookmark'), 'cbxwpbookmarkcategory'); ?>
                            </div>
							<?php $bookmark_category->display() ?>
							<?php do_action('cbxwpbookmark_cats_listing_form_end'); ?>
                        </form>
						<?php do_action('cbxwpbookmark_cats_listing_after'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>