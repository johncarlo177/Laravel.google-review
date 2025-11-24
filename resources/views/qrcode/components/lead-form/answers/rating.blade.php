<div class="answer rating">

    @foreach ($composer->ratingNumbers() as $number)

    <div class="rating-number">
        {{ $number }}
    </div>

    @endforeach

</div>