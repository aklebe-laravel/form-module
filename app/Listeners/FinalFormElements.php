<?php

namespace Modules\Form\app\Listeners;

use Modules\Form\app\Events\FinalFormElements as FinalFormElementsEvent;

class FinalFormElements
{
    public function handle(FinalFormElementsEvent $event): void
    {
        //switch (true) {
        //    case $event->form instanceof UserProfile:
        //        break;
        //
        //    default:
        //        break;
        //}
    }
}