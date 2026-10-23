<?php

namespace Modules\Form\app\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

class BeforeRenderForm
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var NativeObjectBase
     */
    public NativeObjectBase $form;

    /**
     * @param  NativeObjectBase  $form
     */
    public function __construct(NativeObjectBase $form)
    {
        $this->form = $form;
    }
}
