@php
    /** @var \Modules\SystemBase\app\Http\Livewire\BaseComponent $this */
@endphp
<div class="mb-2 messages" wire:click="resetMessages">
    @foreach($this->baseMessages as $key => $_messages)
        @foreach($_messages as $_message)
            <div class="message border rounded-3 font-semibold {{$key}}">
                {{ $_message }}
            </div>
        @endforeach
    @endforeach
</div>
