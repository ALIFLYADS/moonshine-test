<x-moonshine::form.file
    :attributes="$field->attributes()->merge([
        'id' => $field->id(),
        'name' => $field->name(),
    ])"
    :files="is_array($field->formViewValue($item)) ? $field->formViewValue($item) : [$field->formViewValue($item)]"
    :removable="$field->isRemovable()"
    :imageable="false"
    :download="$field->canDownload()"
    :path="$field->path('')"
/>
