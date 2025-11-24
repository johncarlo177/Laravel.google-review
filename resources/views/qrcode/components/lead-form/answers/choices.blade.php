<div class="answer choices {{ $composer->isMultiple() ? 'multiple' : ''}}">

    @foreach ($composer->choices() as $i => $choice)
    @php
    $j = $i + 1;
    @endphp
    <div class="choice choice-{{ $j }}">
        <div class="choice-number choice-number-{{ $j }}">
            {{ $j }}
        </div>
        <div class="choice-text">{{ $choice }}</div>
    </div>
    @endforeach

</div>