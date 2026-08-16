<script>
    'use strict';

    $(document).on('click', '.read-notification', function (e) {
        const $link = $(this);
        const $card = $link.closest('.notification-card');

        if (! $card.hasClass('notification-card--unread')) {
            return;
        }

        e.preventDefault();

        const notificationId = $link.data('id');
        const url = "{{ route('admin.notifications.markAsRead', ':id') }}".replace(':id', notificationId);
        const target = $link.attr('href');

        $.get(url)
            .done(function () {
                $card
                    .removeClass('notification-card--unread')
                    .addClass('notification-card--read');
                $card.find('.notification-card__avatar-dot').remove();
                $card.find('.notification-card__badge').remove();

                if (target && target !== '#' && target.indexOf('javascript:') !== 0) {
                    window.location.href = target;
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.error('Error marking as read:', textStatus, errorThrown);
            });
    });
</script>
