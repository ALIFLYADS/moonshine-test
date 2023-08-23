@props([
    'uniqueId' => 'slider',
    'fromAttributes' => $attributes,
    'toAttributes' => $attributes,
    'fromName',
    'toName',
    'fromValue',
    'toValue',
    'fromField' => $fromName,
    'toField' => $toName
])
<div {{ $attributes->class(['form-group-range'])->only('class') }}>
    <div x-data="range({{ '`'.$fromValue.'`,`'.$toValue.'`' }})"
         x-init="mintrigger(); maxtrigger()"
         data-min="{{ $attributes->get('min', 0) }}"
         data-max="{{ $attributes->get('max', 1000) }}"
         data-step="{{ $attributes->get('step', 1) }}"
         class="form-group-range-wrapper"
    >
        <div>
            <x-moonshine::form.input
                type="range"
                step="{{ $attributes->get('step', 1) }}"
                x-bind:min="min"
                x-bind:max="max"
                x-on:input="mintrigger"
                x-model="minValue"
                x-on:change="onChangeField($event)"
                :attributes="$fromAttributes->merge([
                    'name' => $fromName,
                    'class' => 'form-range-input'
                ])"
            />

            <x-moonshine::form.input
                type="range"
                step="{{ $attributes->get('step', 1) }}"
                x-bind:min="min"
                x-bind:max="max"
                x-on:input="maxtrigger"
                x-model="maxValue"
                x-on:change="onChangeField($event)"
                :attributes="$toAttributes->merge([
                    'name' => $toName,
                    'class' => 'form-range-input'
                ])"
            />

            <div class="form-range-slider">
                <div class="form-range-tracker"></div>
                <div class="form-range-connect" x-bind:style="'right:'+maxthumb+'%; left:'+minthumb+'%'"></div>
                <div class="form-range-thumb" x-bind:style="'left: '+minthumb+'%'"></div>
                <div class="form-range-thumb" x-bind:style="'right: '+maxthumb+'%'"></div>
            </div>
        </div>

        <div class="form-group-range-fields">
            <x-moonshine::form.input
                type="number"
                maxlength="5"
                step="{{ $attributes->get('step', 1) }}"
                x-bind:min="min"
                x-bind:max="max"
                x-on:input="mintrigger"
                x-model="minValue"
                x-on:change="onChangeField($event)"
                :attributes="$fromAttributes->merge([
                    'name' => $fromName,
                ])"
            />

            <x-moonshine::form.input
                type="number"
                step="{{ $attributes->get('step', 1) }}"
                maxlength="5"
                x-bind:min="min"
                x-bind:max="max"
                x-on:input="maxtrigger"
                x-model="maxValue"
                x-on:change="onChangeField($event)"
                :attributes="$toAttributes->merge([
                    'name' => $toName,
                ])"
            />
        </div>
    </div>
</div>
