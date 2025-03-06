import sort from '@alpinejs/sort'

Alpine.plugin(sort)

import {SortableMultiSelect} from "./sortableMultiSelect";

window.SortableMultiSelect = SortableMultiSelect;

/**
 *
 */
export class Form {


    /**
     *
     */
    modelName = null;

    /**
     *
     */
    isLoaded = false;

    /**
     *
     */
    editObjectId = 0;

    /**
     *
     */
    object = {
        'name': '?'
    };

    /**
     *
     */
    formHtml = '';

    /**
     *
     */
    isFileDrop = false;

    /**
     *
     */
    constructor(modelName) {
        let formInstance = this;
        formInstance.modelName = modelName;
    }

    /**
     * Drag & Drop called from "file-uploade.blade.php"
     *
     * @param e
     * @param component
     */
    handleFileDrop = function (e, component) {

        let formInstance = this;
        let object = formInstance.object;

        if (e.dataTransfer.files.length > 0) {
            const files = e.dataTransfer.files;
            component.uploadMultiple('files', files, (uploadedFilename) => {
                object.final_url = uploadedFilename.slice(-1);
            }, () => {
            }, (event) => {
            });
        }
    }

    /**
     * Test falsy inclusive "0"
     *
     * @param v
     * @returns {boolean}
     */
    isValueEmpty(v) {
        if (!v) return true;
        let i = parseInt(v);
        if (isNaN(i)) {
            // a valued string ...
            // do not trim


            if (v === '_no_choice_') { // app('system_base')::selectValueNoChoice
                return true;
            }

            //
            return false;
        }
        return !i;
    }

} // no ; at the end of class declaration