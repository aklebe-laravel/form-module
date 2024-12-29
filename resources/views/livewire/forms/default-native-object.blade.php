@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;

    /** @var NativeObjectBase $this */

    $editForm = $this->getFormResult();

    $readonly = data_get($this, 'readonly', false);
    $actionable = data_get($this, 'actionable', true);
    $showFormActions = ($actionable && $this->formActionButtons);

    $editFormHtml = data_get($editForm, 'additional.form_html', '');
    $editFormObject = data_get($editForm, 'additional.form_object');
    $description = data_get($editForm, 'additional.form_object.description');
    $title = data_get($editForm, 'additional.form_object.title');
    $editFormModelObject = data_get($editFormObject, 'object');
@endphp
<div>
    @include('inc.messages')

    @if($this->isFormOpen)
        {{--Scroll to Form on every update/open/visible--}}
        <div x-show="scrollToForm();"></div>
        @if($editFormHtml)
            @include('form::inc.form-backdrop')

            <div class="card dt-edit-form {{ ($readonly) ? 'readonly' : 'editable' }}"
                 @if ($this->autoXData)
                     x-data="{form_data:$wire.formObjectAsArray}"
                 @endif
                 @if($this->canKeyEnterSendForm)
                     wire:keydown.enter="{{ $this->getDefaultWireFormAccept() }}"
                    @endif
            >
                <div class="card-body">
                    @if ($title)
                        <div class="card-header">
                            <span class="decent">{{ $title }}</span>
                        </div>
                    @endif
                    <div class="card-text">
                        @if ($description)
                            <div class="alert alert-light">
                                {{ $description }}
                            </div>
                        @endif
                        <div>
                            {!! $editFormHtml !!}
                        </div>
                        @if ($showFormActions)
                            <hr/>
                            <div class="container">
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
        @else
            <div class="alert alert-warning">Empty Form HTML!</div>
        @endif
    @endif
</div>