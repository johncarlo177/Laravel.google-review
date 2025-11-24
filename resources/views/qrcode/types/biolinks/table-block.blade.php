<div class="block table-block" id="{{ $model->getId() }}">
    <table>
        @foreach ($block->table_data() as $row)
            <tr>
                @foreach ($row as $cell)
                    <td>
                        {{ $cell }}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
</div>
