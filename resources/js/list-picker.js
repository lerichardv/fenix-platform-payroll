/**
 * ListPicker
 * A class to build a tool to select items from one list, visual and intuitive
 * usage:
 * listPicker = new ListPicker([{id:1, name: "Item 1"}, ...], "#wrapper");
 *
 * Please view the ./readme.md for more information.
 *
 * @author lerichard
 * @contact ricardo.valladares.triminio@gmail.com
 */
class ListPicker {
    items = [];
    selectedItems = [];
    wrapperSelector = '';
    _wrapperElement;
    _listItemActive;
    _listItemsWrapper;
    _listSelectedItemsWrapper;
    _listPickerActionsWrapper;
    _listPickerButtonAddAll;
    _listPickerButtonRemoveAll;
    constructor(items = [], wrapperSelector = '#list-picker-wrapper') {
        this.wrapperSelector = wrapperSelector;
        if (items.length == 0) {
            let inlineItems = document.querySelector(this.wrapperSelector).getAttribute('data-items');
            if (inlineItems != null && inlineItems != '') {
                try {
                    let parsedItems = JSON.parse(inlineItems);
                    items = parsedItems;
                } catch (e) {
                    console.log(inlineItems);
                    console.error('El formato del atributo data-items no es correcto. Ej. [{"id":"1","item":"Item1"},{"id":"2","item":"Item2"}] ...]');
                }

            }
        }
        this.items = items;
        this._buildListPicker();
    }
    addItem = (item) => {
        this.items.push(item);
        this.render();
    }
    removeItem = (item) => {
        let itemIndex = null;
        this.items.find((it, index) => {
            if (it == item.id) {
                itemIndex = index;
            }
        });
        if (itemIndex) {
            this.items.splice(itemIndex, 1);
        }
        this.render();
    }
    selectItem = (item) => {
        let indexToRemove = this.items.findIndex((it) => {
            return it.id == item.id;
        });
        this.items.splice(indexToRemove, 1);
        this.selectedItems.push(item);
        this.render();
    }
    selectItems = (itemOrArray) => {
        console.log({ itemOrArray });
        const itemsToSelect = Array.isArray(itemOrArray) ? itemOrArray : [itemOrArray];
        itemsToSelect.forEach(item => {
            let indexToRemove = this.items.findIndex((it) => it.id == item.id);
            if (indexToRemove !== -1) {
                this.items.splice(indexToRemove, 1);
                this.selectedItems.push(item);
            }
        });
        this.render();
    }
    unSelectItem = (item) => {
        let indexToRemove = this.selectedItems.findIndex((it) => {
            return it.id == item.id;
        });
        this.selectedItems.splice(indexToRemove, 1);
        this.items.unshift(item);
        this.render();
    }
    render = () => {
        this._listItemsWrapper.innerHTML = "";
        this._listSelectedItemsWrapper.innerHTML = "";
        this.items.forEach((item) => {
            this._listItemsWrapper.appendChild(this._generateItemElement(item));
        });
        this.selectedItems.forEach((item) => {
            this._listSelectedItemsWrapper.appendChild(this._generateSelectedItemElement(item));
        })
    }
    selected = () => {
        return this.selectedItems;
    }
    destroy = () => {
        this.items = [];
        this.selectedItems = [];
        this._wrapperElement.innerHTML = "";
    }
    reset = () => {
        this.selectedItems.forEach((item) => {
            this.items.push(item);
        });
        this.selectedItems = [];
        const sortedItems = this.items.sort((a, b) => {
            if (a.name.toLowerCase() < b.name.toLowerCase()) {
                return -1;
            }
            if (a.name.toLowerCase() > b.name.toLowerCase()) {
                return 1;
            }
            return 0;
        });
        this.items = sortedItems;
        this.render();
    }
    selectAll = () => {
        this.reset();
        this.selectedItems = this.items;
        this.items = [];
        this.render();
    }
    removeAll = () => {
        this.reset();
    }
    _buildListPicker = () => {
        this._wrapperElement = document.querySelector(this.wrapperSelector);
        let baseElements = `
            <div class="list-picker-grid">
                <div class="list-picker-items-wrapper" id="list-picker-items-wrapper">
                </div>
                <div class="list-picker-selected-items-wrapper" id="list-picker-selected-items-wrapper">
                </div>
            </div>`;
        let actionsWrapper = `
            <div class="list-picker-actions-wrapper" id="list-picker-actions-wrapper">
            </div>
        `;
        let addAllButton = `
            <button type="button" class="btn-list-picker-add-all" id="btn-list-picker-add-all">
                Add all <i class="fa-solid fa-arrow-right"></i>
            </button>
            `;
        let removeAllButton = `
            <button type="button" class="btn-list-picker-remove-all" id="btn-list-picker-remove-all">
                <i class="fa-solid fa-arrow-left"></i> Remove all
            </button>
        `;
        this._wrapperElement.innerHTML = baseElements + actionsWrapper;
        this._listItemsWrapper = document.querySelector(this.wrapperSelector + " #list-picker-items-wrapper");
        this._listSelectedItemsWrapper = document.querySelector(this.wrapperSelector + " #list-picker-selected-items-wrapper");
        this._listPickerActionsWrapper = this._wrapperElement.querySelector(this.wrapperSelector + ' #list-picker-actions-wrapper');
        this._listPickerActionsWrapper.innerHTML = addAllButton + removeAllButton;
        this._listPickerButtonAddAll = this._listPickerActionsWrapper.querySelector(this.wrapperSelector + ' #btn-list-picker-add-all');
        this._listPickerButtonRemoveAll = this._listPickerActionsWrapper.querySelector(this.wrapperSelector + ' #btn-list-picker-remove-all');
        this._listPickerButtonAddAll.addEventListener('click', () => {
            this.selectAll();
        });
        this._listPickerButtonRemoveAll.addEventListener('click', () => {
            this.removeAll();
        });
        this.render();
    }
    _generateItemElement = (item) => {
        let elementString = `
            <div class="list-picker-item">
                <div class="">
                    <input type="hidden" id="list-picker-item-id" value="${item['id']}">
                    ${item['name']}
                </div>
                <div>
                    <button type="button" class="list-picker-item-add-button">
                        Add <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        `;
        let element = this._createElementFromHTML(elementString);
        element.addEventListener("click", () => {
            this.selectItem(item);
        });
        return element;
    }
    _generateSelectedItemElement = (item) => {
        let elementString = `
            <div class="list-picker-item selected">
                <div class="">
                    <input type="hidden" id="list-picker-item-id" value="${item['id']}">
                    ${item['name']}
                </div>
                <div>
                    <button type="button" class="list-picker-item-remove-button">
                        <i class="fa-solid fa-chevron-left"></i> Remove
                    </button>
                </div>
            </div>
        `;
        let element = this._createElementFromHTML(elementString);
        element.addEventListener("click", () => {
            this.unSelectItem(item);
        });
        return element;
    }
    _createElementFromHTML(htmlString) {
        var div = document.createElement('div');
        div.innerHTML = htmlString.trim();
        // Change this to div.childNodes to support multiple top-level nodes.
        return div.firstChild;
    }
}

export default ListPicker;
