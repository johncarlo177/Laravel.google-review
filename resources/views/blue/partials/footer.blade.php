{!! ContentManager::customCode('Footer: Above Footer') !!}

<div class="layout-gap"></div>

@if (!has_custom_frontend())

    <footer class="website-footer">
        <div class="layout-box">
            <div class="footer-wrapper">
                <div class="footer-logo-block">
                    @include('blue.components.logo', ['inverse' => true])
                    {!! ContentManager::contentBlocks('Footer: below logo.') !!}
                    <qrcg-language-picker></qrcg-language-picker>
                </div>
                @foreach ($composer->menu() as $gi => $group)
                    <div class="list">
                        <h3>{{ $composer->groupName($group, $gi) }}</h3>
                        <ul>
                            @foreach ($group['items'] as $i => $item)
                                <li>
                                    <a href="{{ $composer->itemLink($item, $i, $gi) }}" @if (!empty($item['target'])) target="{{ $item['target'] }}" @endif>
                                        {{ $composer->label($item, $i, $gi) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
            <div class="footer-copy">
                {!! ContentManager::contentBlocks('Footer Copyrights') !!}
            </div>
        </div>
    </footer>
@endif

{!! ContentManager::customCode('Footer: Below Footer') !!}
