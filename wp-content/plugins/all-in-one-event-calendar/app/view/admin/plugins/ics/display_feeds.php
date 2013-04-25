<p>
<?php _e(
    'Configure which other calendars your own calendar subscribes to.
    You can add any calendar that provides an iCalendar (.ics) feed.
    Enter the feed URL(s) below and the events from those feeds will be
    imported periodically.',
    AI1EC_PLUGIN_NAME ); ?>
</p>
<div id="ics-alerts"></div>
<label class="textinput" for="cron_freq">
  <?php _e( 'Check for new events', AI1EC_PLUGIN_NAME ) ?>:
</label>
<?php echo $cron_freq ?>
<br class="clear" />

<div id="ai1ec-feeds-after" class="ai1ec-feed-container">
	<h4 class="ai1ec_feed_h4"><?php _e( 'iCalendar/.ics Feed URL:', AI1EC_PLUGIN_NAME ) ?></h4>
	<div class="ai1ec-feed-url"><input type="text" name="ai1ec_feed_url" id="ai1ec_feed_url" /></div>
	<div class="ai1ec-feed-category">
		<label for="ai1ec_feed_category">
			<?php _e( 'Event category', AI1EC_PLUGIN_NAME ); ?>:
		</label>
		<?php echo $event_categories; ?>
	</div>
	<div class="ai1ec-feed-tags">
		<label for="ai1ec_feed_tags">
			<?php _e( 'Tag with', AI1EC_PLUGIN_NAME ); ?>:
		</label>
		<input type="text" name="ai1ec_feed_tags" id="ai1ec_feed_tags" />
	</div>
	<div class="ai1ec-feed-comments-enabled">
		<label for="ai1ec_comments_enabled">
			<input type="checkbox" name="ai1ec_comments_enabled"
				id="ai1ec_comments_enabled" value="1" />
			<?php _e( 'Allow comments on imported events', AI1EC_PLUGIN_NAME ); ?>
		</label>
	</div>
	<div class="ai1ec-feed-map-display-enabled">
		<label for="ai1ec_map_display_enabled">
			<input type="checkbox" name="ai1ec_map_display_enabled"
				id="ai1ec_map_display_enabled" value="1" />
			<?php _e( 'Show map on imported events', AI1EC_PLUGIN_NAME ); ?>
		</label>
	</div>
	<input type="button" id="ai1ec_add_new_ics" class="button" value="<?php _e( '+ Add new subscription', AI1EC_PLUGIN_NAME ) ?>" />
</div>

<?php 
echo $feed_rows;
echo $modal->render();
?>

<?php submit_button( esc_attr__( 'Update Settings', AI1EC_PLUGIN_NAME ), 'primary', 'ai1ec_save_settings' ); ?>
