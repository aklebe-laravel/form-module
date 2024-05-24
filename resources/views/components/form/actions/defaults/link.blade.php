@php
    /**
     * @var \Modules\Form\app\Http\Livewire\Form\Base\ModelBase $this
     * @var Illuminate\Database\Eloquent\Model $editFormModelObject
     * @var string $linkName
     * @var string $linkAddress
     */
@endphp
<a class="underline text-sm text-gray-600 hover:text-gray-900 mr-2" href="{{ $linkAddress }}">
    {{ $linkName }}
</a>