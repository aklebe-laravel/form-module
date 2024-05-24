@php
    /** ...
    * @var bool $disabled
    * @var string $name name attribute
    * @var string $label
    * @var \Illuminate\Database\Eloquent\Collection $value
    * @var bool $read_only
    * @var string $description
    * @var string $css_classes
    * @var string $x_model optional for alpine.js
    * @var string $xModelName
    * @var array $html_data data attributes
    * @var array $x_data
    * @var string $modelName
    * @var \Modules\Form\app\Forms\Base\ModelBase $form_instance
    */

    $options['table_options']['selectable'] = false;
    $options['table_options']['enabledCollectionNames'] = [ // enable only the selected table
        \Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable::COLLECTION_NAME_SELECTED_ITEMS => true,
    ];
@endphp
@include('form::components.form.element-dt-split-with-form')