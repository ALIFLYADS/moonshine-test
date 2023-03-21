@props([
    'resource',
    'field',
    'item'
])
@if($field->isHidden())
    <div class="hidden">{{ $slot }}</div>
@else
    <x-moonshine::form.input-wrapper
        :attributes="$field->attributes()->merge([
            'x-show' => $field->showWhenState ? 'whenFields.'.$field->showWhenField . '==`'.$field->showWhenValue.'`' : 'true'
        ])"
        label="{{ $field->label() }}"
        name="{{ $field->name() }}"
        :expansion="$field->ext()"
    >
        @if($field->getHint())
            <x-moonshine::form.hint>
                {{ $field->getHint() }}
            </x-moonshine::form.hint>
        @endif

        {{ $slot }}
    </x-moonshine::form.input-wrapper>

    @if($field->hasLink())
        <x-moonshine::link
            class="mb-4"
            :href="$field->getLinkValue()"
            :_target="$field->isLinkBlank() ? 'blank' : 'self'"
        >
            {{ $field->getLinkName() }}
        </x-moonshine::link>
    @endif
@endif
