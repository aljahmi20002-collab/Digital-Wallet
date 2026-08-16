<div class="user-mgmt-stats" aria-label="{{ __('User statistics') }}">
    @foreach($stats as $stat)
        <div class="um-stat">
            <div class="um-stat__icon {{ $stat['color_class'] }}">
                <x-icon name="{{ $stat['icon'] }}" width="26" height="26"/>
            </div>

            <div class="um-stat__copy text-end">
                <div class="um-stat__value">{{ $stat['value'] }}</div>
                <div class="um-stat__title">{{ $stat['title'] }}</div>
            </div>
        </div>
    @endforeach
</div>
