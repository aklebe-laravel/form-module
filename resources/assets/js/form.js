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
    modelName = 'Base';

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

} // no ; at the end of class declaration