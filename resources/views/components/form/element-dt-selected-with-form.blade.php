@php
    use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    data_set($data,'options.table_options.selectable', false);
    data_set($data,'options.table_options.enabledCollectionNames', [ // enable only the selected table
        BaseDataTable::COLLECTION_NAME_SELECTED_ITEMS => true,
    ]);
@endphp
@include('form::components.form.element-dt-split-with-form')