/**
 *
 */
export class SortableMultiSelect {

    /**
     *
     * @type {[]}
     */
    selectOptionItems = [];

    /**
     *
     * @type {{}}
     */
    selectOptionValues = [];

    /**
     *
     * @type {boolean}
     */
    debug = false;

    /**
     *
     * @param config
     * @param wire
     */
    constructor(config, wire) {
        this.selectOptionItems = config.selectOptionItems ?? [];
        this.selectOptionValues = config.selectOptionValues ?? [];
        this.debug = config.debug;

        this.selectOptionValues = wire ?? [];
    }

    /**
     *
     * @param sourceId id in select option (the value="" part)
     * @param destinationIndex int start from 0 for new position
     */
    sortSelectOptionTo(sourceId, destinationIndex) {

        let newSelectOptionArray = [];
        let i = 0;

        // find item by source id
        let foundSourceItem = null;
        let foundSourceIndex = 0;
        for (let arrItem of this.selectOptionItems) {
            if (arrItem.id === sourceId) {
                foundSourceItem = arrItem;
                foundSourceIndex = i;
                break;
            }
            i++;
        }

        i = 0;
        if (foundSourceItem !== null) {
            if (foundSourceIndex < destinationIndex) {
                destinationIndex++;
            }
            for (let arrItem of this.selectOptionItems) {
                if (i === destinationIndex) {
                    newSelectOptionArray.push(foundSourceItem);
                }
                if (arrItem.id !== sourceId) {
                    newSelectOptionArray.push(arrItem);
                }
                i++;
            }
            // again if last
            if (i === destinationIndex) {
                newSelectOptionArray.push(foundSourceItem);
            }

            // console.log(optionValues);
            this.selectOptionItems = newSelectOptionArray;

            this.updateSelectOptionValues();
        }
    }

    /**
     *
     */
    updateSelectOptionValues() {
        let optionValues = [];
        for (let arrItem of this.selectOptionItems) {
            if (arrItem.selected) {
                optionValues.push(arrItem.id);
            }
        }
        this.selectOptionValues = optionValues;
    }

    /**
     *
     * @param id
     */
    toggleOption(id) {
        for (let arrItem of this.selectOptionItems) {
            if (arrItem.id === id) {
                arrItem.selected = !arrItem.selected;
                break;
            }
        }
        this.updateSelectOptionValues();
    }

} // no ; at the end of class declaration