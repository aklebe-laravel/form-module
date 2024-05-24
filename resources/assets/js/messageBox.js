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
            this.defaultActions = {
                'accept-rating': '<button type="button" class="btn btn-outline-secondary" x-on:click="messageBox.goAction(\'accept-rating\')">' + this.parent.trans('Accept') + '</button>',
                'cancel': '<button type="button" class="btn btn-outline-secondary" x-on:click="messageBox.isOpen = false">' + this.parent.trans('Close') + '</button>',
                'save': '<button type="button" class="btn btn-primary">Save changes</button>',
                'delete-item': '<button type="button" class="btn btn-danger" x-on:click="messageBox.goAction(\'delete-item\')">' + this.parent.trans('Delete') + '</button>',
                'telegram-delete-me': '<button type="button" class="btn btn-danger" x-on:click="messageBox.goAction(\'telegram-delete-me\')">' + this.parent.trans('Delete') + '</button>',
                'simulate-item': '<button type="button" class="btn btn-success" x-on:click="messageBox.goAction(\'simulate-item\')">' + this.parent.trans('Simulate Item') + '</button>',
                'launch-item': '<button type="button" class="btn btn-danger" x-on:click="messageBox.goAction(\'launch-item\')">' + this.parent.trans('Launch Item') + '</button>',
                'send-email': '<button type="button" class="btn btn-danger" x-on:click="messageBox.goAction(\'send-email\')">' + this.parent.trans('Send Email') + '</button>',
                'launch': '<button type="button" class="btn btn-danger" x-on:click="messageBox.goAction(\'launch\')">' + this.parent.trans('Launch') + '</button>',
                'claim': '<button type="button" class="btn btn-primary" x-on:click="messageBox.goAction(\'claim\')">' + this.parent.trans('Claim User') + '</button>',
                'create-offer-binding': '<button type="button" class="btn btn-primary" x-on:click="messageBox.goAction(\'create-offer-binding\')">' + this.parent.trans('Create Offer Binding') + '</button>',
                'offer-suspend': '<button type="button" class="btn btn-danger" x-on:click="messageBox.goAction(\'offer-suspend\')">' + this.parent.trans('Suspend') + '</button>',
                'reject-offer': '<button type="button" class="btn btn-danger" x-on:click="messageBox.goAction(\'reject-offer\')">' + this.parent.trans('Reject Offer') + '</button>',
                're-offer': '<button type="button" class="btn btn-secondary" x-on:click="messageBox.goAction(\'re-offer\')">' + this.parent.trans('Create New Offer') + '</button>',
                'accept-offer': '<button type="button" class="btn btn-primary" x-on:click="messageBox.goAction(\'accept-offer\')">' + this.parent.trans('Accept Offer') + '</button>',
            };
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