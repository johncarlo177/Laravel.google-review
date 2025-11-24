@if (@$item['variations'] && is_array(@$item['variations']))
    <select name="selected_variation">
        @foreach ($item['variations'] as $variation)
            @php
                $id    = @$variation['id'];
                $name  = @$variation['name'];
                $price = @$variation['price'];
            @endphp
            <option variation-price="{{ $price }}" variation-name="{{ $name }}" variation-id="{{ $id }}">
                {{ $name }} - {{ $price }} 
            </option>   
        @endforeach
    </select>
@else
    <div class="menu-item-price">
        {!! @$item['price'] !!}
    </div>
@endif