/**
 *
 * @returns {*|{parent: null, isOpen: boolean, defaultActions: {}, start(*): void, show(*, *): void, callbackParams: *[], title: string, actions: *[], config: *, content: string, getConfig(*): *, goAction(*): void}}
 */
export function messageBox() {

    return {

        /**
         * Parent main object (like class Website)
         */
        parent: null,

        /**
         *
         */
        isOpen: false,

        /**
         *
         */
        title: '',

        /**
         *
         */
        content: '',

        /**
         *
         */
        actions: [],

        // /**
        //  * configPath of last show()
        //  */
        // configPath: '',

        /**
         * params of last show()
         */
        callbackParams: [],

        /**
         * declaration in start()
         */
        defaultActions: {},

        /**
         * Config of prepared message boxes (declared in app.blade)
         */
        config: messageBoxConfig,

        /**
         *
         * @param parameterParent
         */
        start(parameterParent) {
            // console.log('MessageBox started ...');
            this.parent = parameterParent;
            this.defaultActions = appData.messageBoxes;
        },

        getConfig(configPath) {
            return this.parent.getValue(this.config, configPath);
        },

        /**
         * params differ by content/caller
         *
         * @param configPath
         * @param callbackParams callback parameters
         */
        show(configPath, callbackParams) {
            let selfInstance = this;

            let boxConfig = this.getConfig(configPath);
            if (!boxConfig) {
                console.error('Path not supported/implemented: ' + configPath);
                return;
            }
            this.title = this.parent.trans(this.parent.getValue(boxConfig, 'title'));

            let newActions = this.parent.getValue(boxConfig, 'actions');
            let newActionsFinal = [];
            newActions.forEach(function (item) {
                if (typeof item === 'string' || item instanceof String) {
                    item = selfInstance.defaultActions[item];
                }
                newActionsFinal.push(item);
            });
            this.actions = newActionsFinal;
            this.callbackParams = callbackParams;

            let fetchContent = null;
            // if 'fetch-content' exists ...
            if (fetchContent = this.parent.getValue(boxConfig, 'fetch-content')) {

                let route = fetchContent + '/' + this.parent.getValue(callbackParams, 'product.id');
                this.parent.requestGet(route)
                    .then(data => {
                        this.content = this.parent.getValue(data, 'data.html');
                        this.isOpen = true;
                    });
            } else {
                this.content = this.parent.trans(this.parent.getValue(boxConfig, 'content'));
                this.isOpen = true;
            }
        },

        /**
         *
         */
        goAction(key) {
            if (key in this.callbackParams) {

                let callbackParams = this.callbackParams[key];
                let livewireComponentName = this.parent.getValue(callbackParams, 'name')
                let livewireId = this.parent.getValue(callbackParams, 'livewire_id', '0')
                let itemId = this.parent.getValue(callbackParams, 'item_id', '0')
                // @todo: params generic/dynamic?
                Livewire.dispatchTo(livewireComponentName, key, {'livewireId':livewireId, 'itemId':itemId});
                this.isOpen = false;
                this.content = '';
            } else {
                console.error('Key not found: ' + key + ' in callbackParams.');
            }
        }
    }

} // no ; at the end of class declaration