{{--validation error notification--}}
@if ( !$errors->isEmpty() && $errors->any())
    @php
           notifyEvs('error', $errors->first());
           session()->flash('errors', $errors);
           session()->save();
    @endphp
@endif

@session('notifyevs')
    <script>
        (function () {
            'use strict';
            const notifyData = @json(session('notifyevs'));
            let attempts = 0;

            const showNotify = function () {
                const notify = typeof window.notifyEvs === 'function'
                    ? window.notifyEvs
                    : (typeof notifyEvs === 'function' ? notifyEvs : null);

                if (notify) {
                    notify(notifyData.type, notifyData.message);
                    return;
                }

                if (attempts < 100) {
                    attempts += 1;
                    window.setTimeout(showNotify, 80);
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showNotify, { once: true });
            } else {
                showNotify();
            }
        })();
    </script>
@endsession
