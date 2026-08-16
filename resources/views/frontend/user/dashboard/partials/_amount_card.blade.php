<div class="single-amount-card-area mb-3">
    <div class="amount-wrapper">
        <div class="amount-scroller d-lg-none" id="amountCardsRow">
        @foreach($statistics as $index => $statistic)
            <div class="amount-item">
                <div class="single-amount-card">
                    <div class="media">
                        <div class="media-left icon-container {{ $statistic['color_class'] }}">
                            <x-icon name="{{ $statistic['icon'] }}" class="icon"/>
                        </div>
                        <div class="media-body align-self-center">
                            <h6>{{ $statistic['value'] }}</h6>
                            <span>{{ $statistic['title'] }}</span>
                        </div>
                    </div>
                    @if(isset($statistic['link']))
                        <a href="{{ $statistic['link'] }}">
                            <x-icon name="arrow-icon" class="icon"/>
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
        </div>

        {{-- Desktop grid (visible on lg and up) --}}
        <div class="row d-none d-lg-flex mt-3">
            @foreach($statistics as $statistic)
                <div class="col-xl-4 col-lg-6">
                    <div class="single-amount-card">
                        <div class="media">
                            <div class="media-left icon-container {{ $statistic['color_class'] }}">
                                <x-icon name="{{ $statistic['icon'] }}" class="icon"/>
                            </div>
                            <div class="media-body align-self-center">
                                <h6>{{ $statistic['value'] }}</h6>
                                <span>{{ $statistic['title'] }}</span>
                            </div>
                        </div>
                        @if(isset($statistic['link']))
                            <a href="{{ $statistic['link'] }}">
                                <x-icon name="arrow-icon" class="icon"/>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
