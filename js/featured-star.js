jQuery(document).ready(function ($) {
    const __ = wp.i18n.__;

    const o = {
        chars: {
            active: "★",
            inactive: "☆",
        },

        toggle: function ($postId, _wpnonce, active, success) {
            const data = {
                post_ID: $postId,
                _wpnonce,
                active,
                action: 'featured_star_toggle',
            };

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data,
                success: function (t) {
                    success(t == 1);
                },
            });
        },
    };

    $('.column-featured a').each(function () {
        const $this = $(this);
        $this.on("click", function () {
            const postId = $this.data('post-id');
            const nonce = $this.data('nonce');
            const active = !$this.hasClass('active');
            o.toggle(postId, nonce, active, function (toogle) {
                $this.toggleClass('active', toogle);
                $this.html(toogle ? o.chars.active : o.chars.inactive);
            });
        });
    });
});
