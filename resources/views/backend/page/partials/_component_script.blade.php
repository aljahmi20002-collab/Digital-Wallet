<script>
    (function ($) {
        "use strict";

        $(document).ready(function () {
            const $componentList = $('#componentList');
            const $pageComponent = $('#pageComponent');
            const $dropText = $('.drop-text');
            const $componentSearch = $('#componentSearch');

            function getToggleIcon(type) {
                if (type === 'remove') {
                    return `
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12H19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                        </svg>
                    `;
                }

                return `
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                    </svg>
                `;
            }

            function setActionState($item, type) {
                const $action = $item.find('.manage-drag');
                const $toggleIcon = $item.find('.toggle-icon');
                const isRemove = type === 'remove';

                $toggleIcon.html(getToggleIcon(type));
                $action
                    .toggleClass('pc-icon-btn--success', !isRemove)
                    .toggleClass('pc-icon-btn--danger', isRemove)
                    .attr('title', isRemove ? 'Remove from Page' : 'Add to Page');
            }

            function updateDropTextVisibility() {
                $dropText.toggleClass('d-none', $pageComponent.find('.item').length > 0);
                updateComponentListEmptyState();
            }

            function showEmptyMessage(type) {
                let $msg = $('.component-empty-text');
                if (!$msg.length) {
                    $msg = $('<div>').addClass('component-empty-text').appendTo($componentList);
                }
                $msg.text(
                    type === 'notfound' ? 'No components matched your search.' :
                        type === 'empty' ? 'No available components to add.' : ''
                ).removeClass('d-none');
            }

            function hideEmptyMessage() {
                $('.component-empty-text').addClass('d-none');
            }

            function updateComponentListEmptyState() {
                const $allItems = $componentList.find('.item');
                const $visibleItems = $allItems.filter(function () {
                    return !$(this).hasClass('d-none');
                });
                const searchText = $componentSearch.val().trim();

                if ($allItems.length === 0) {
                    showEmptyMessage(searchText ? 'notfound' : 'empty');
                } else if ($visibleItems.length === 0) {
                    showEmptyMessage(searchText ? 'notfound' : 'empty');
                } else {
                    hideEmptyMessage();
                }
            }

            $componentSearch.on('input', function () {
                const searchText = $(this).val().toLowerCase();
                $componentList.find('.item').each(function () {
                    const name = $(this).data('name') || '';
                    $(this).toggleClass('d-none', !name.includes(searchText));
                });
                updateComponentListEmptyState();
            });

            new Sortable($componentList[0], {
                group: 'shared',
                animation: 200,
                sort: false,
                onAdd: function (evt) {
                    const $item = $(evt.item);
                    setActionState($item, 'add');
                    updateDropTextVisibility();
                    updateComponentListEmptyState();
                    tooltipTriger();
                }
            });

            new Sortable($pageComponent[0], {
                group: 'shared',
                animation: 200,
                onAdd: function (evt) {
                    const $item = $(evt.item);
                    setActionState($item, 'remove');
                    updateDropTextVisibility();
                    updateComponentListEmptyState();
                    tooltipTriger();
                }
            });

            $('#componentList, #pageComponent').on('click', '.manage-drag', function () {
                const $item = $(this).closest('.item');
                const $clone = $item.clone(false);
                const isFromList = $(this).closest('#componentList').length > 0;

                $item.remove();
                (isFromList ? $pageComponent : $componentList).append($clone);
                setActionState($clone, isFromList ? 'remove' : 'add');

                updateDropTextVisibility();
                updateComponentListEmptyState();
                tooltipTriger();
            });

            updateDropTextVisibility();
            updateComponentListEmptyState();
        });
    })(jQuery);
</script>
