@php
    use Illuminate\Database\Eloquent\Model;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /**
     * @var NativeObjectBase $this
     * @var Model $editFormModelObject
     * @var string $linkName
     * @var string $linkAddress
     */
@endphp
<a class="underline text-sm text-gray-600 hover:text-gray-900 mr-2" href="{{ $linkAddress }}">
    {{ $linkName }}
</a>