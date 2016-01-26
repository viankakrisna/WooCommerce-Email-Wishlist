jQuery(document)
    .ready(function ($) {
        var $modalBtn = $('#wew-open-modal');
        var $submitBtn = $('#wew-submit');
        var $modal = $('#wew-modal');
        var $document = $(document);

        $modalBtn.on('click', function (e) {
            e.preventDefault();
            Modal.open({
                content: $modal.html(),
                draggable: true,
                width: '35em'
            });
            var $form = $('#wew-form');
            var $parent = $form.parents('#modal-container');
            $parent.addClass('wew');
        });
        $document.on('submit', '#wew-form', function (e) {
            e.preventDefault();
            var $this = $(this);
            var $user = $('#wew-user');
            var $target = $('#wew-target');
            var $message = $('#wew-message');
            var $nonce = $('#wew-nonce');
            var $form = $('#wew-form');
            var $modalContent = $('#modal-content');
            var $id = $('#wew-id');
            var url = window.wew_ajax.url;
            var data = {
                action: 'wew_send_wishlist',
                user: $user.val(),
                target: $target.val(),
                message: $message.val(),
                nonce: $nonce.val(),
                id: $id.val()
            };
            $this.addClass('whirl');
            $.post(url, data, function (res) {
                $modalContent.html(res.message);
            });
        });
    });
