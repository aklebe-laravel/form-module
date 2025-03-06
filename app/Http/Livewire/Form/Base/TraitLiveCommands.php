<?php

namespace Modules\Form\app\Http\Livewire\Form\Base;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Modules\Form\app\Services\FormService;

trait TraitLiveCommands
{
    const int viewModeSimple = 500;
    const int viewModeDefault = 1000;
    const int viewModeExtended = 9000;

    /**
     * defaults for $this->liveCommandsConfig
     */
    const array liveFilterConfigDefaults = [
        'reload'      => false,
        'default'     => null,
        'view'        => null,
        'view_params' => [],
    ];

    /**
     * Nested assoc array of elements updated by livewire updating().
     * This will be used to control the form like switch a mode or something.
     * Values will also be saved in session separated by form name.
     * Names of form elements if input will prefix with 'liveCommands.xxx.yyy'
     *
     * @var array|string[]
     */
    public array $liveCommands = [];

    /**
     * additional data for liveCommands.
     * Possible keys see liveFilterConfigDefaults.
     *
     * @var array
     */
    public array $liveCommandsConfig = [];

    /**
     * Overwrite this to declare form specific liveCommands
     *
     * @return void
     */
    protected function initLiveCommands(): void
    {
    }

    /**
     * @param  string  $suffixKey
     *
     * @return string
     */
    protected function getLiveCommandsSessionFullKey(string $suffixKey): string
    {
        return 'form.'.$this->getName().'.liveCommands.'.$suffixKey;
    }

    /**
     * @param  string      $suffixKey
     * @param  mixed|null  $default
     *
     * @return mixed
     */
    protected function getLiveCommandsSession(string $suffixKey, mixed $default = null): mixed
    {
        return Session::get($this->getLiveCommandsSessionFullKey($suffixKey), $default);
    }

    /**
     * @param  string  $suffixKey
     * @param  mixed   $value
     *
     * @return void
     */
    protected function setLiveCommandsSession(string $suffixKey, mixed $value): void
    {
        Session::put($this->getLiveCommandsSessionFullKey($suffixKey), $value);
    }

    /**
     * @param  string  $suffixKey
     *
     * @return void
     */
    protected function forgetLiveCommandsSession(string $suffixKey): void
    {
        Session::forget($this->getLiveCommandsSessionFullKey($suffixKey));
    }

    /**
     * the updating process used for all liveCommands
     *
     * @param $property
     * @param $value
     *
     * @return void
     */
    public function liveCommandsUpdating($property, $value): void
    {
        $propertyPrepared = Str::chopStart($property, 'liveCommands.');
        if (Arr::has($this->liveCommands, $propertyPrepared)) {
            $this->setLiveCommand($propertyPrepared, $value);

            // reload is for store switches for example
            $reload = data_get($this->liveCommandsConfig, $propertyPrepared.'.reload', false);

            // reopen ...
            $this->reopenFormIfNeeded($reload); // true is important to reload all values!
        }
    }

    /**
     * @param  int  $default
     *
     * @return void
     */
    protected function addViewModeCommand(int $default = self::viewModeDefault): void
    {
        /** @var FormService $formService */
        $formService = app(FormService::class);

        $key = 'controls_set_view_mode';
        $config = [
            'reload'      => false,
            'default'     => $default,
            'view'        => 'form::components.form.select',
            'view_params' => $formService::getFormElementFormViewMode(),
        ];
        $this->initLiveCommand($key, $config);
    }

    /**
     * @return void
     */
    protected function addReloadCommand(): void
    {
        $key = 'controls_reload';
        $config = [
            'reload' => true, // that's the only task of this button
            'view'   => 'form::components.form.reload',
        ];
        $this->initLiveCommand($key, $config);
    }

    /**
     * @param  int  $atLeast
     *
     * @return bool
     */
    public function viewModeAtLeast(int $atLeast = self::viewModeDefault): bool
    {
        return data_get($this->liveCommands, 'controls_set_view_mode', self::viewModeDefault) >= $atLeast;
    }

    /**
     * @param  int  $atMax
     *
     * @return bool
     */
    public function viewModeAtMaximum(int $atMax = self::viewModeDefault): bool
    {
        return data_get($this->liveCommands, 'controls_set_view_mode', self::viewModeDefault) <= $atMax;
    }

    /**
     * Set the liveFilter value and update the session.
     *
     * @param  string  $propertyPrepared
     * @param  mixed   $value
     *
     * @return void
     */
    protected function setLiveCommand(string $propertyPrepared, mixed $value): void
    {
        data_set($this->liveCommands, $propertyPrepared, $value);

        // Also update session for this form type ...
        $this->setLiveCommandsSession($propertyPrepared, $value);
    }

    /**
     * @param  string      $propertyPrepared
     * @param  mixed|null  $default
     *
     * @return mixed
     */
    protected function getLiveCommand(string $propertyPrepared, mixed $default = null): mixed
    {
        return data_get($this->liveCommands, $propertyPrepared, $default);
    }

    /**
     * @param  string  $propertyPrepared
     *
     * @return bool
     */
    protected function hasLiveCommand(string $propertyPrepared): bool
    {
        return Arr::has($this->liveCommands, $propertyPrepared);
    }

    /**
     * @return bool
     */
    protected function hasLiveCommands(): bool
    {
        return !!$this->liveCommands;
    }

    /**
     * @param  string  $propertyPrepared
     * @param  array   $config
     *
     * @return void
     */
    protected function initLiveCommand(string $propertyPrepared, array $config = []): void
    {
        $x = self::liveFilterConfigDefaults;
        $this->liveCommandsConfig[$propertyPrepared] = app('system_base')->arrayMergeRecursiveDistinct($x, $config);

        data_set($this->liveCommandsConfig, $propertyPrepared.'.reload', data_get($config, 'reload'));
        // Use session if exists. Otherwise, use a default.
        $v = (int) $this->getLiveCommandsSession($propertyPrepared, data_get($config, 'default'));
        $this->setLiveCommand($propertyPrepared, $v);
    }

}