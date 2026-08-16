/**
 * Notify the user with a message of a certain type.
 *
 * @param {string} type - The type of the notification.
 * @param {string} message - The message to display.
 */
function notifyEvs(type, message) {
    "use strict";

    notifyEvsRuntime.show(type, message);
}

var notifyEvsRuntime = (function () {
    "use strict";

    var pending = [];
    var notifyPatched = false;
    var pollHandle = null;
    var MAX_POLL_ATTEMPTS = 250;

    function patchNotifyOnce() {
        if (notifyPatched || typeof Notify !== 'function' || !Notify.prototype) {
            return notifyPatched;
        }

        // simple-notify auto-closes a toast as soon as IntersectionObserver
        // reports intersectionRatio === 0. observe() always fires once with
        // the initial state, and during the 500ms slide-in animation the
        // wrapper can still be off-screen — which kills the toast before
        // the user sees it. autoclose + autotimeout already handle dismissal,
        // so we neutralize the observer-based close path here.
        Notify.prototype.setObserver = function () {};
        notifyPatched = true;

        return true;
    }

    function present(type, message) {
        var title = String(type || '');
        var isMobileViewport = typeof window !== 'undefined'
            && typeof window.matchMedia === 'function'
            && window.matchMedia('(max-width: 575.98px)').matches;
        var notifyGap = isMobileViewport ? 8 : 20;
        var notifyDistance = isMobileViewport ? 10 : 20;
        const notifyPosition = isMobileViewport ? 'x-center top' : 'right top';

        new Notify({
            status: type,
            title: title.charAt(0).toUpperCase() + title.slice(1),
            text: message,
            effect: 'slide',
            speed: 500,
            customClass: '',
            customIcon: '',
            showIcon: true,
            showCloseButton: true,
            autoclose: true,
            autotimeout: 5000,
            gap: notifyGap,
            distance: notifyDistance,
            type: 1,
            position: notifyPosition,
            customWrapper: '',
        });
    }

    function flushPending() {
        if (typeof Notify !== 'function') {
            return;
        }
        patchNotifyOnce();
        while (pending.length > 0) {
            var entry = pending.shift();
            try {
                present(entry.type, entry.message);
            } catch (err) {
                if (window.console && typeof console.error === 'function') {
                    console.error('notifyEvs: failed to render notification', err);
                }
            }
        }
    }

    function startPolling() {
        if (pollHandle !== null || typeof window === 'undefined') {
            return;
        }
        var attempts = 0;
        var tick = function () {
            if (typeof Notify === 'function') {
                pollHandle = null;
                flushPending();

                return;
            }
            attempts += 1;
            if (attempts >= MAX_POLL_ATTEMPTS) {
                pollHandle = null;
                if (window.console && typeof console.warn === 'function') {
                    console.warn(
                        'notifyEvs: simple-notify never loaded; dropped '
                        + pending.length + ' notification(s).'
                    );
                }
                pending.length = 0;

                return;
            }
            pollHandle = window.setTimeout(tick, 80);
        };
        pollHandle = window.setTimeout(tick, 80);
    }

    function show(type, message) {
        if (typeof Notify === 'function') {
            patchNotifyOnce();
            flushPending();
            try {
                present(type, message);
            } catch (err) {
                if (window.console && typeof console.error === 'function') {
                    console.error('notifyEvs: failed to render notification', err);
                }
            }

            return;
        }

        pending.push({ type: type, message: message });
        startPolling();
    }

    return { show: show };
})();

if (typeof window !== 'undefined') {
    window.notifyEvs = notifyEvs;
}

// Function to disable the submit button on form submission
function disableSubmitButton(form, message = 'Processing...') {
    const submitButton = form.querySelector('.submit-btn');
    if (submitButton) {
        submitButton.disabled = true; // Disable the button
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> ' + message + ''; // Optional: Show processing state
    }
}


function validateNumber(value) {
    "use strict";
    const pattern = /^[0-9]*$/; // Allow only numbers
    return pattern.test(value) ? value : value.replace(/[^0-9]/g, '');
}

function validateDouble($value) {
    "use strict";
    return $value.replace(/[^0-9.]/g, '')
        // Remove any additional decimal points.
        .replace(/(\..*?)\..*/g, '$1');
}

function readURL(input, imagePreview) {
    'use strict';
    // Check if there is a selected file
    if (input.files && input.files[0]) {
        // Create a new FileReader
        var reader = new FileReader();
        // Define the onload event
        reader.onload = function (e) {
            // Set the background image of the image preview element
            imagePreview.css('background-image', 'url(' + e.target.result + ')');
            // Hide the image preview and then fade it in
            imagePreview.hide().fadeIn(400);
        };
        // Read the data URL of the selected file
        reader.readAsDataURL(input.files[0]);
    }
}

function withJQuery(callback) {
    if (typeof window !== 'undefined' && typeof window.jQuery === 'function') {
        callback(window.jQuery);

        return;
    }

    if (typeof document !== 'undefined') {
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.jQuery === 'function') {
                callback(window.jQuery);
            }
        });
    }
}

// Function to handle image preview for dynamically added elements
window.handleImagePreview = function handleImagePreview() {
    'use strict';
    if (typeof window.jQuery !== 'function') {
        return;
    }

    const $ = window.jQuery;
    var hostname = window.location.hostname;
    $(".imageUpload").off('change.image-preview').on('change.image-preview', function () {
        var previewId = $(this).data("preview-id");
        var imagePreview = $("#" + previewId);
        readURL(this, imagePreview);
    });
    $(".imageRemove").off('click.image-preview').on('click.image-preview', function (event) {
        var previewId = $(this).prev().data("preview-id");
        var imagePreview = $("#" + previewId);

        // Change to default placeholder image using the hostname
        imagePreview.css('background-image', 'url(http://' + hostname + '/general/static/default/placeholder.png)');
        // Set value to indicate removal
        var imageInput = $("#" + previewId + "-remove");
        imageInput.val('coevs-remove');

        var imageNameInput = $("#" + previewId + "_upload");
        imageInput.attr('name', imageNameInput.attr('name'));
    });
}

withJQuery(function ($) {
    window.handleImagePreview();

    $(document).on('click', '.delete', ({target}) => {
        // Ensure we're getting the closest .delete button element
        const url = $(target).closest('.delete').data('url');

        // Check if the URL exists
        if (url) {
            // Set the action attribute of the #delete-form-modal element to the retrieved URL
            $('#delete-form-modal').attr('action', url);

            // Display the #delete_modal modal
            $('#delete_modal').modal('show');
        } else {
            console.error('URL not found for the delete action.');
        }
    });
});


function tooltipTriger() {
    'use strict';

    // Remove all tooltip DOM manually (prevent leftover ghost tooltips)
    document.querySelectorAll('.tooltip').forEach(el => el.remove());

    const tooltipTriggerList = document.querySelectorAll('.modal-tooltip');
    tooltipTriggerList.forEach(el => {
        const existing = coreui.Tooltip.getInstance(el);
        if (existing) {
            existing.dispose();
        }
        new coreui.Tooltip(el);

    });
}

function initializeSummernote(selector) {
    $(selector).summernote({
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture']],
            ['view', ['help']],
        ],
        styleTags: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        placeholder: 'Write Your Message',
        height: 200,
        focus: true,
        callbacks: {
            onImageUpload: function (files) {
                for (let i = 0; i < files.length; i++) {
                    uploadImageToServer(files[i], $(this));
                }
            },
            onMediaDelete: function (target) {
                deleteImageFromServer(target[0].src);
            }
        }
    });

    // Apply custom styles to editable content
    $('.note-editable').css('font-weight', '400');
}


// Modal Dynamic Content Loading
editFormByModal = function (modalShowId, modalDataAppendId, isFile = true, tooltip = false) {
    const $modal = $('#' + modalShowId);
    const $modalContent = $('#' + modalDataAppendId);

    $(document).on('click', '.edit-modal', function () {
        const url = $(this).data('edit-url');
        const loadingHtml = `
            <div class="d-flex justify-content-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;

        $modal.modal('show');
        $modalContent.html(loadingHtml);

        $.get(url, function (data) {
            $modalContent.html(data);
            initializeSummernote($modalContent.find('.summernote'));

            if (tooltip) tooltipTriger();
            if (isFile) handleImagePreview();
        });
    });
};

function dayMap(day) {
    if (typeof day === "string" && isNaN(Number(day))) {
        return day;
    }
    const days = {
        1: 'Sunday',
        2: 'Monday',
        3: 'Tuesday',
        4: 'Wednesday',
        5: 'Thursday',
        6: 'Friday',
        7: 'Saturday'
    };
    const dayNum = Number(day);
    return days[dayNum] || day;
}

function slugify(text) {
    return text.toString().normalize('NFD')  // unicode normalize
        .replace(/[\u0300-\u036f]/g, '')     // remove accents
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')        // remove non-alphanum
        .trim()
        .replace(/\s+/g, '-')                // replace spaces with -
        .replace(/-+/g, '-');                // remove multiple hyphens
}

function uploadImageToServer(file, editor) {
    let formData = new FormData();
    formData.append('file', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    $.ajax({
        url: window.location.origin + '/summernote/image-upload',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            editor.summernote('insertImage', response.url);
        },
        error: function (xhr) {
            console.error('Image upload failed.', xhr.responseText);
        }
    });
}

function deleteImageFromServer(imageUrl) {
    $.ajax({
        url: window.location.origin + '/summernote/image-delete',
        type: 'POST',
        data: {
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            url: imageUrl
        },
        error: function (xhr) {
            console.error('Image delete failed.', xhr.responseText);
        }
    });
}
