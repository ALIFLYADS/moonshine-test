@props([
    'buttons',
    'formName',
    'errors' => false,
    'precognitive' => false,
    'raw' => false
])

@if($errors->{$formName})
    <x-moonshine::form.all-errors :errors="$errors->{$formName}" />
@endif

<form
    {{ $attributes->merge(['class' => 'form', 'method' => 'POST']) }}
>
    @if(strtolower($attributes->get('method', '')) !== 'get')
        @csrf
    @endif

    {{ $slot }}

    @if(!$raw)
        <x-moonshine::grid>
            <x-moonshine::column>
                <div class="mt-3 flex w-full flex-wrap justify-start gap-2">
                    {{ $buttons ?? '' }}
                </div>
            </x-moonshine::column>

            @if($precognitive)
                <x-moonshine::column>
                    <div class="precognition_errors mb-6"></div>
                </x-moonshine::column>
            @endif
        </x-moonshine::grid>
    @endif
</form>
