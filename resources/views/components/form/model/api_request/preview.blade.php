<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="api-content-object-tab" data-toggle="tab" href="#api-content-object" role="tab" aria-controls="api-content-object" aria-selected="true">Objekt</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="api-content-request-tab" data-toggle="tab" href="#api-content-request" role="tab" aria-controls="api-content-request"
           aria-selected="false">Request</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="api-content-response-tab" data-toggle="tab" href="#api-content-response" role="tab" aria-controls="api-content-response" aria-selected="false">Response</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="api-content-extra-tab" data-toggle="tab" href="#api-content-extra" role="tab" aria-controls="api-content-extra" aria-selected="false">Extra Data</a>
    </li>
</ul>
<div class="tab-content request-info">
    <div class="tab-pane fade show active" id="api-content-object" role="tabpanel" aria-labelledby="api-content-object-tab">{{ $form_instance }}</div>
    <div class="tab-pane fade" id="api-content-request" role="tabpanel" aria-labelledby="api-content-request-tab">{{ $request }}</div>
    <div class="tab-pane fade" id="api-content-response" role="tabpanel" aria-labelledby="api-content-response-tab">{{ $response }}</div>
    <div class="tab-pane fade" id="api-content-extra" role="tabpanel" aria-labelledby="api-content-extra-tab">{{ $extraData }}</div>
</div>
