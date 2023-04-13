@props([
    'fetchApiRoute',
    'fetchApiUriKey',
    'fetchApiField',
    'searchable' => false,
    'nullable' => false,
    'values' => [],
    'options' => false
])
<select
    {{ $attributes->merge([
        'class' => 'form-select',
        'x-data' => sprintf('selectFetchApi("%s","%s","%s")', $fetchApiRoute, $fetchApiUriKey, $fetchApiField),
        'data-search-enabled' => $searchable,
        'data-remove-item-button' => $nullable
    ]) }}
>
    @if($options ?? false)
        {{ $options }}
    @else
        @foreach($values as $value => $label)
            <option @selected($value == $attributes->get('value', ''))
                    value="{{ $value }}"
            >
                {{ $label }}
            </option>
        @endforeach
    @endif
</select>
