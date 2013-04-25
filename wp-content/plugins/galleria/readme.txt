=== Galleria for Wordpress ===

Tags: flickr
Requires at least: 2.6
Tested up to: 3.1
Stable tag: 1.0

Display Flickr items in a javascript gallery. Based upon the Galleria javascript gallery (http://galleria.aino.se/)

== Usage ==

After installing the plugin, use the following shortcodes in pages and posts:

=== galleria ===

Displays the Galleria javascript gallery with your photostream, or, if the user accesses the 
URL with an anchor tag containing a photoset ID, the contents of that photoset.

Parameters:
   api_key     Your flickr API key (get one here: http://www.flickr.com/services/apps/create/noncommercial/)
   account     Your flickr username
   
Sample:

[galleria api_key="YOUR-API-KEY" account="your-user-name"]


=== galleria_photosets ===

Displays a nested list of your groups and photosets, with links to click to open the photoset in the current page.
Should be used on the same page as the galleria shortcode, above.  This list is generated client-side using Javascript.
Note that this means the list will not be picked up by search engine crawlers.

Parameters
   api_key     Your flickr API key (get one here: http://www.flickr.com/services/apps/create/noncommercial/)
   user_id     Your flickr user ID

Sample:

[galleria_photosets api_key="YOUR-API-KEY" user_id="1234567@N00"]