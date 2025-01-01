@php
    /**
     * ratingContainer properties:
     * show_value: bool - also show the real value behind the stars
     * clickable: bool - decides the stars can be clicked (to rate it)
     * user_has_rated: bool - mark user has already rated or not
     */
    $ratingAlpineName = $ratingAlpineName ?? 'ratingContainer.rating5';
@endphp
<div class="text-nowrap">
    {{-- {{ $ratingAlpineName }}: must be set in x-data and can be differ when used by form --}}
    {{-- show_value: if true also show value like "(4,5)" (default: true) --}}
    <span x-data="{is_clickable: (typeof ratingContainer.clickable !== 'undefined') ? ratingContainer.clickable : false}"
          :class="[({{ $ratingAlpineName }} < 1) ? 'text-secondary decent' : (({{ $ratingAlpineName }} >= 4) ? 'text-success' : (({{ $ratingAlpineName }} < 2.5) ? 'text-danger' : 'text-warning')),(is_clickable ? 'cursor-pointer' : '')]"
    >
        <template x-for="rating5Climber in 5">
            <span>
                <template x-if="is_clickable">
                    <span x-on:click="{{ $ratingAlpineName }}=rating5Climber">
                        <i class="bi"
                           :class="({{ $ratingAlpineName }} + 0.1 >= rating5Climber) ? 'bi-star-fill' : ((rating5Climber - {{ $ratingAlpineName }} <= 0.5) ? 'bi-star-half' : 'bi-star')"></i>
                    </span>
                </template>
                <template x-if="!is_clickable">
                   <span>
                       <i class="bi"
                          :class="({{ $ratingAlpineName }} + 0.1 >= rating5Climber) ? 'bi-star-fill' : ((rating5Climber - {{ $ratingAlpineName }} <= 0.5) ? 'bi-star-half' : 'bi-star')"></i>
                   </span>
                </template>
            </span>
        </template>
    </span>

    {{--draw the value as number--}}
    <template x-if="((typeof ratingContainer.show_value === 'undefined') || ratingContainer.show_value) && (typeof {{ $ratingAlpineName }} !== 'undefined')">
        <span class="decent">
            (<span x-text="{{ $ratingAlpineName }}.toFixed(2)"></span>)
        </span>
    </template>

    {{--draw the check if user has rated--}}
    <template x-if="typeof ratingContainer.user_has_rated !== 'undefined' && ratingContainer.user_has_rated">
        <span class="user-already-rated-icon-block text-success">
            <span class="bi bi-check"></span>
        </span>
    </template>
    <template x-if="typeof ratingContainer.user_has_rated !== 'undefined' && !ratingContainer.user_has_rated">
        <span class="user-already-rated-icon-block text-secondary">
            <span class="bi bi-lightbulb"></span>
        </span>
    </template>
</div>