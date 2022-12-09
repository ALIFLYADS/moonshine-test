@foreach($items as $item)
    <tr style="{{ $resource->trStyles($item, $loop->index) }}">
        <td class="px-6 py-4 whitespace-nowrap" style="{{ $resource->tdStyles($item, $loop->index, 0) }}">
            <input class="actionBarCheckboxRow" @change='actionBar("item")' name="items[{{ $item->getKey() }}]"
                   type="checkbox" value="{{ $item->getKey() }}"/>
        </td>

        @foreach($resource->indexFields() as $index => $field)
            <td class="px-6 py-4 whitespace-nowrap"
                style="{{ $resource->tdStyles($item, $loop->index, $index + 1) }}"
            >
                <div class="text-sm leading-5">{!! $field->indexViewValue($item) !!}</div>
            </td>
        @endforeach

        <td class="px-6 py-4 whitespace-nowrap text-right text-sm leading-5 font-medium"
            style="{{ $resource->tdStyles($item, $loop->index, count($resource->indexFields()) + 1) }}"
        >
            @include("moonshine::base.index.shared.item_actions", ["item" => $item, "resource" => $resource])
        </td>
    </tr>
@endforeach
