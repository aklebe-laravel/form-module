/**
 *
 * @returns {{parent: null, fetchObject(): Promise<*>, start(*): void, getFetchObjectUrl(): string, isLoaded: boolean, userId: number, object: {}}|Promise<unknown>|string}
 */
export function user() {

    return {

        /**
         * Parent main object (like class Website)
         */
        parent: null,

        /**
         *
         */
        isLoaded: false,

        /**
         *
         */
        userId: 0,

        /**
         *
         */
        object: {},

        /**
         *
         */
        start(parameterParent) {
            this.parent = parameterParent;
        },


        /**
         *
         */
        getFetchObjectUrl() {
            let userInstance = this;
            return `/user/get/${userInstance.userId}`
        },

        /**
         * Returns a promise. You can do fetchObject().then( ...)
         *
         * @returns {Promise<unknown>}
         */
        fetchObject() {
            return new Promise((resolve, reject) => {
                let userInstance = this;
                userInstance.isLoaded = false;

                this.parent.requestGet(userInstance.getFetchObjectUrl())
                    .then(data => {
                        userInstance.isLoaded = true;
                        console.log('User loaded ...');
                        // console.log(data);
                        userInstance.object = data.data;
                        userInstance.parent.crossSelling.items = [...userInstance.object.cross_selling_products];
                        resolve(userInstance);
                    }).catch(data => {
                    reject(data);
                });
            });
        },

    }

} // no ; at the end of class declaration