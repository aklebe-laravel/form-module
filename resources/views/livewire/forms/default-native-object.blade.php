@php
    use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;
    use Modules\Form\app\Services\FormService;

    /** @var NativeObjectBase|ModelBase $this */

    /** @var FormService $formService */
    $formService = app(FormService::class);

    $_formErrors = $_formErrors ?? [];
    if (!($editForm = $this->dataSource)) {
        $_formErrors[] = 'Missing Form Result';
    }

    $readonly = data_get($this, 'readonly', false);
    $actionable = data_get($this, 'actionable', true);
    $showFormActions = ($actionable && $this->formActionButtons);

    if (!$this->formHtml || !$this->finalFormElements) {
        $_formErrors[] = __("Permission denied or empty form");
    }

    $description = data_get($this->finalFormElements, 'description');
    if ($description) {
        $description = nl2br(trim($description));
    }
    $title = data_get($this->finalFormElements, 'title');
    $formShortId = app('system_base')->getSimpleClassName($this::class);

    $simpleModelName = ($this instanceof ModelBase) ? app('system_base')->getSimpleClassName($this->getObjectEloquentModelName()) : null;
    $isOwnUser = $this->isOwnUser();
@endphp
<div class="form-container dt-form-type-{{ $formShortId }}" x-data="getNewForm('{{ $simpleModelName }}')">
    <!-- Loading Overlay -->
    <div wire:loading.delay.longest.flex
         class="form-loading-overlay hidden fixed inset-0 z-[100] items-center justify-center bg-surface-200/60 backdrop-blur-sm">
        @include('form::components.loading-overlay')
    </div>

    @include('form::inc.messages')

    @if($this->isFormOpen)
        {{--Scroll to Form on every update/open/visible--}}
        <div x-show="scrollToForm();"></div>
        @if ($_formErrors && config('app.debug', false))
            @foreach($_formErrors as $_formError)
                <div class="alert alert-warning">{{ $_formError }}</div>
            @endforeach
        @endif

        @if($this->formHtml)
            @include('form::inc.form-backdrop')

            <div
                    @if ($this->autoXData) x-data="{form_data:$wire.dataTransfer}" @endif
            class="card dt-edit-form {{ ($readonly || !$showFormActions) ? 'readonly' : 'editable' }}"
                    @if($this->canKeyEnterSendForm)
                        wire:keydown.enter="{{ $this->getDefaultWireFormAccept() }}"
                    @endif
            >
                <div class="card-body">
                    <div class="card-header">
                        <div class="grid grid-cols-2 gap-4  items-center">
                            <div class="col-span-2 @if($this->hasLiveCommands()) md:col-span-1 @endif">
                                @if ($title)
                                    <div class="form-title text-info flex">
                                        <span class="{{ $isOwnUser ? 'text-success-700' : 'text-danger-700' }}" title="{{ $isOwnUser ? __('Owner') : __('You are not the owner') }}">
                                            <x-lucide-user class="w-5 h-5 mr-2"/>
                                        </span>
                                        <span>{{ $title }}</span>

                                    </div>
                                @else
                                    @if(($editFormModelObject ?? null) && $editFormModelObject->id)
                                        <span class="decent">{{ __('???') }}</span>
                                        @if($readonly)
                                            - <span class="decent">{{ __("ID") }}: {{ $editFormModelObject->id }}</span>
                                        @endif
                                    @else
                                        <span class="decent">{{ __('New Item') }}</span>
                                    @endif
                                @endif
                            </div>
                            @if($this->hasLiveCommands())
                                <div class="col-span-2 md:col-span-1 text-end">
                                    @foreach($this->liveCommandsConfig as $__k => $__v)
                                        <div class="d-inline-flex">
                                            @include($__v['view'], ['data' => app('system_base')->arrayMergeRecursiveDistinct(NativeObjectBase::defaultViewData, $__v['view_params']), 'form_instance' => $this])
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    </div>
                    <div class="card-text">
                        @if ($description)
                            <div class="alert alert-warning">
                                {!! $description !!}
                            </div>
                        @endif
                        <div>
                            {!! $this->formHtml !!}
                        </div>
                        @if ($showFormActions)
                            <hr/>
                            <div class="form-actions text-right">
                                @foreach($this->formActionButtons as $formActionButtonView)
                                    @include($formActionButtonView)
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>