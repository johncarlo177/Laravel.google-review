<div class="block audio-block" id="{{ $model->getId() }}">

    <audio controls>
        <source src="{{ $model->fileUrl('audio_file') }}">
    </audio>
</div>
