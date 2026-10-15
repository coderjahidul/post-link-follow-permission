jQuery(document).ready(function($) {
    var wp_inline_edit = inlineEditPost.edit;
    inlineEditPost.edit = function(post_id) {
        wp_inline_edit.apply(this, arguments);

        var id = 0;
        if (typeof(post_id) == 'object') {
            id = parseInt(this.getId(post_id));
        }

        if (id > 0) {
            var specific_post = $('#post-' + id),
                follow_nofollow_value = specific_post.find('.column-follow').text().trim().toLowerCase();

            if (follow_nofollow_value === 'nofollow') {
                $('select[name="follow_nofollow"]', '.inline-edit-row').val('nofollow');
            } else {
                $('select[name="follow_nofollow"]', '.inline-edit-row').val('follow');
            }
        }
    };
});
