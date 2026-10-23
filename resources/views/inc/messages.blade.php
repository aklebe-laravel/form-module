@php
    use Modules\SystemBase\app\Http\Livewire\BaseComponent;

    /** @var BaseComponent $this */
@endphp
<div class="my-2 messages" wire:click="resetMessages">
    @foreach($this->baseMessages as $key => $_messages)
        @foreach($_messages as $_message)
            <div class="message border rounded-3 font-semibold alert alert-{{$key}}">
                {{ $_message }}
            </div>
        @endforeach
    @endforeach
</div>
