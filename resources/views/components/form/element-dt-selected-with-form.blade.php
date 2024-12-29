@php
    use Illuminate\Database\Eloquent\Collection;
    use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
    use Modules\Form\app\Forms\Base\NativeObjectBase;

    /**
    * @var bool $disabled
    * @var string $name name attribute
    * @var string $label
    * @var Collection $value
    * @var bool $read_only
    * @var string $description
    * @var string $css_classes
    * @var string $x_model optional for alpine.js
    * @var string $xModelName
    * @var array $html_data data attributes
    * @var array $x_data
    * @var string $modelName
    * @var NativeObjectBase $form_instance
    */

    $options['table_options']['selectable'] = false;
    $options['table_options']['enabledCollectionNames'] = [ // enable only the selected table
        BaseDataTable::COLLECTION_NAME_SELECTED_ITEMS => true,
    ];
@endphp
@include('form::components.form.element-dt-split-with-form')