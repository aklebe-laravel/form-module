@php
    use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;

    /** @var ModelBase $this */

    $_formErrors = [];
    if (!($editForm = $this->getFormResult())) {
        $_formErrors[] = 'Missing Form Result';
    }

    $readonly = data_get($this, 'readonly', false);
    $actionable = data_get($this, 'actionable', true);
    $showFormActions = ($actionable && $this->formActionButtons);

    if (!($editFormHtml = data_get($editForm, 'additional.form_html', ''))) {
        $_formErrors[] = 'Empty Form HTML!';
    }

    if (!($editFormObject = data_get($editForm, 'additional.final_form_elements'))) {
        $_formErrors[] = 'Missing final_form_elements';
    }

    $description = data_get($editForm, 'additional.final_form_elements.description');
    if ($description) {
        $description = nl2br(trim($description));
    }
    $title = data_get($editForm, 'additional.final_form_elements.title');
    if (!($editFormModelObject = $this->dataTransfer)) {
        $_formErrors[] = 'Missing form data';
    }
@endphp
<div>
    <!-- Loading Overlay -->
    <div wire:loading.delay>
        @include('form::components.loading-overlay')
    </div>

    @include('form::inc.messages')

    @if($this->isFormOpen)
        {{--Scroll to Form on every update/open/visible--}}
        <div x-show="scrollToForm();"></div>

        @if ($_formErrors && config('app.debug', false))
            <div class="alert alert-danger">Form <strong>isFormOpen = true</strong>, but issues in: {{ 'default-model-base.blade.php' }}</div>
            @foreach($_formErrors as $_formError)
                <div class="alert alert-warning">{{ $_formError }}</div>
            @endforeach
        @endif

        @if($editFormHtml)
            @include('form::inc.form-backdrop')

            <div @if ($this->autoXData) x-data="{form_data:$wire.dataTransfer}" @endif
            class="card dt-edit-form {{ ($readonly || !$showFormActions) ? 'readonly' : 'editable' }}"
                 @if($this->canKeyEnterSendForm)
                     wire:keydown.enter="{{ $this->getDefaultWireFormAccept() }}"
                    @endif
            >
                <div class="card-body">
                    <div class="card-header">
                        @if ($title)
                            <span class="decent">{{ $title }}</span>
                        @else
                            @if($editFormModelObject && $editFormModelObject->id)
                                <span class="decent">{{ __($this->getEloquentModelName()) }}</span>
                                @if($readonly)
                                    - <span class="decent">{{ __("ID") }}: {{ $editFormModelObject->id }}</span>
                                @endif
                            @else
                                <span class="decent">{{ __('New Item') }}</span>
                            @endif
                        @endif
                    </div>
                    <div class="card-text">
                        @if ($description)
                            <div class="alert alert-warning">
                                {!! $description !!}
                            </div>
                        @endif
                        <div>
                            {!! $editFormHtml !!}
                        </div>
                        @if ($showFormActions)
                            <hr/>
                            <div class="container form-actions">
                                <div class="row">
                                    <div class="col-12 text-end">
                                        @foreach($this->formActionButtons as $formActionButtonView)
                                            @include($formActionButtonView)
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>