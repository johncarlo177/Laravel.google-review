<div class="block copyable-data-block" id="{{ $model->getId() }}">

    <div class="label">
        {!! $model->field('label') !!}
    </div>

    <div class="value">
        <div class=text>
            {!! $model->field('value') !!}
        </div>
        <qrcg-copy-icon>{!! $model->field('value') !!}</qrcg-copy-icon>
    </div>

</div>
