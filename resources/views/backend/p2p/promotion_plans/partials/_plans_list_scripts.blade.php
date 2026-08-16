<script>
    "use strict";

    document.addEventListener('DOMContentLoaded', function () {
        const sortableTarget = document.getElementById('p2p-package-sortable');
        if (!sortableTarget || typeof Sortable === 'undefined' || !sortableTarget.querySelector('tr[data-id]')) {
            return;
        }

        new Sortable(sortableTarget, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'bg-light',
            onEnd: function () {
                const positions = [];

                $('#p2p-package-sortable tr').each(function (index) {
                    const id = $(this).data('id');
                    if (id) {
                        positions.push({
                            id: id,
                            order: index + 1
                        });
                    }
                });

                if (positions.length === 0) {
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.p2p.promotion-packages.position-update') }}",
                    method: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        positions: positions
                    },
                    success: function (response) {
                        notifyEvs('success', response.message || "{{ __('Package order updated successfully.') }}");
                    },
                    error: function () {
                        notifyEvs('error', "{{ __('Unable to update package order right now.') }}");
                    }
                });
            }
        });
    });
</script>
