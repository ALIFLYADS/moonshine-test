<style>
    .toggle-checkbox:checked {
        @apply: right-0 border-green-400;
        right: 0;
        border-color: #7843E9;
    }
    .toggle-checkbox:checked + .toggle-label {
        @apply: bg-green-400;
        background-color: #7843E9;
    }
</style>

<div x-data='{checked : {{ $field->getOnValue() == $field->formViewValue($item) ? 'true' : 'false'}}}' class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
    <input type="hidden"
           name="{{ $field->name() }}"
           :value="checked ? '{{ $field->getOnValue() }}' : '{{ $field->getOffValue() }}'"
           value="{{ $field->getOnValue() == $field->formViewValue($item) ? '1' : '0'}}">

    <input @change='checked=!checked'
           {{ $field->isDisabled() ? "disabled" : "" }}
           {{ $field->getOnValue() == $field->formViewValue($item) ? 'checked' : ''}}
           :value="checked ? '{{ $field->getOnValue() }}' : '{{ $field->getOffValue() }}'"
           value="{{ $field->getOnValue() }}"
           type="checkbox"
           name="fake_{{ $field->name() }}"
           id="{{ $field->id() }}"
           class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
    />

    <label for="{{ $field->id() }}" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer">

    </label>
</div>