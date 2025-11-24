@if (!empty($hours) && is_array($hours))
    @foreach ($hours as $day => $fields)
        @if (is_object($fields))
            <div class="row {{ $composer->isOpeningHourAdditional($day) ? 'additional' : '' }}">
                <div class="key">{{ $composer->openingHourDayName($day) }}</div>
                <div class="value">
                    @if ($fields->enabled)
                        {{ $fields->from }} - {{ $fields->to }}
                    @else
                        {{ t('Closed') }}
                    @endif
                </div>
            </div>
        @endif
    @endforeach
@endif
