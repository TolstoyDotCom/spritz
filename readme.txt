=== Spritz ===
Contributors: wisdomtreedev
Tags: workflow, management, editorial, admin, collaboration
Requires at least: 6.6
Tested up to: 6.6
Stable tag: 1.0.0
Requires PHP: 8.3
License: Apache-2.0
License URI: https://www.apache.org/licenses/LICENSE-2.0

Spritz reminds you to review or refresh your WordPress posts on a regular basis.


== Description ==
Your older posts can quickly become out-of-date and require editing to provide up-to-date information.
For instance, posts at medical sites might require regular reviews to make sure they're keeping up with the latest research.
Spritz keeps track of when each post needs to be reviewed.
When the time arrives, the post is marked as needing review and an optional email is sent to the admin user.
You can adjust the timing on a per-post basis if you want: some posts might require monthly reviews, others might only need them yearly.
A listing page shows the current state of each post.
You can even subject the review to a further approval step if you want.

To read more about 'Spritz' visit [this page](https://wisdomtree.dev/spritz-wordpress-plugin/).


== Installation ==
1. If you have a previous version installed, deactivate it (but don't uninstall it) and delete the "spritz" directory.
2. Upload the .zip file containing the plugin to your "wp-content/plugins" directory.
3. Unzip the .zip file, creating a "spritz" directory.
4. Activate the "Spritz" plugin on WordPress' 'Plugins' menu.
5. If multiple user roles will administer reviews, also install one of the many user roles plugins. See the section below.


== Configuration ==
A post can be in one of the following states: NEUTRAL, AWAITING_REVIEW, REVIEWED, NEEDS_WORK, APPROVED, or DISAPPROVED.
Each post has a "Next action date".
If the post is in REVIEWED or APPROVED state, it's moved into AWAITING_REVIEW state on the "Next action date".
Then, it's expected that an admin will put the post into the REVIEWED or APPROVED state. After an admin does that, the timer starts over again.
Of course, an admin could also put the post into the NEEDS_WORK or DISAPPROVED state,
but what happens in that case is beyond the scope of this plugin.


== Screenshots ==
1. screenshot-1.png The sidebar that appears on posts.
2. screenshot-2.png The settings page.
3. screenshot-3.png The page listing the latest Spritz for each post.
4. screenshot-4.png Sample permissions (using a user roles module).


== Details ==
Screenshot 1 shows the sidebar where you set the state of your posts.
At the top it shows the current state, the next action date, and the note on the current state.
Below that, you can set the new state and provide notes.
To add the new state, you have to check the confirm checkbox and save the post.
At the bottom you can override the settings just for this post, see below.

Screenshot 2 shows the settings screen.
If you check 'Automatically approve posts', any time a post is put into the REVIEWED state,
it will automatically be moved into the APPROVED state. If you don't check that, an admin
will need to move it into the APPROVED state manually.
'Number of days' controls the timer.
The other settings are for the email that's sent out when a post is moved into the AWAITING_REVIEW state.
If you don't fill out all three email fields, no email will be sent out.

Referring back to Screenshot 1, you can override settings on a per-post basis.
For instance, you might want some posts to be reviewed more frequently than others.
You can also change the auto-approval settings.
As with setting the new state, you have to check the confirm checkbox to have the overrides saved.


== Shortcodes ==
Three shortcodes are provided: [spritz_post_status], [spritz_post_note], and [spritz_post_nextreviewdate].
If used on a post, those show the current status of the post, the last note, and the next review date.
If used on something other than a post, nothing is shown.


== Multiple Roles ==
If you have multiple roles that will review posts, you'll need to install one of the many
member plugins that are available.
Screenshot 4 is from one of those plugins.
Only roles with the 'spritz_edit_settings' permission can change the global settings.
Only roles with the 'spritz_override_post_settings' permission can override those global settings on a per-post basis.
The other permissions are of the form 'spritz_transition_from_X_to_Y'.
You may decide that users of a certain role can move a post from REVIEWED to NEEDS_WORK but not vice versa, and so on.


== Customizations ==
If you'd like a customization for a reasonable fee, please contact us with the details:
https://wisdomtree.dev/contact/feedback


== Support future development ==
Your generous support will help support new versions:
https://www.paypal.com/donate/?hosted_button_id=4U3VYC5LNWRM4


== Changelog =

= [1.0.0] Nov 25, 2024 =
* First release

== Additional Documentation ==
