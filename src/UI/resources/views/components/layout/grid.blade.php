@props([
    'components' => [],
])
<div {{ $attributes->merge(['class' => 'grid grid-cols-12 gap-6 form-group']) }}>
    <x-moonshine::components
        :components="$components"
    />

    {{ $slot ?? '' }}
</div>
