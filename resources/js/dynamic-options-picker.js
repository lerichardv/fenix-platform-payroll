/**
 * DynamicOptionsPicker
 * A class to build a tool to select items from a dropdown list
 * usage:
 * window.group_by = new DynamicOptionsPicker([
 *   { value: "cod_usuario", text: "User ID" },
 *   { value: "cod_usuario", text: "User ID" },
 *   { value: "categoria", text: "User Category" },
 *   { value: "cod_farm_ci", text: "Farm" },
 *   { value: "cod_location_ci", text: "Location" }
 *  ], "#dynamic-group-by-picker-select", "#dynamic-group-by-selected-wrapper");
 *
 *
 * @author lerichard
 * @contact ricardo.valladares.triminio@gmail.com
 */
class DynamicOptionsPicker {

    selectColumn;
    selectedColumnsWrapper;
    selectedItems;
    options;

    constructor(
        initialOptions = [],
        selectColumnSelector = "#dynamic-column-picker-select",
        selectedColumnsWrapperSelector = "#dynamic-columns-selected-wrapper"
    ){
        this.options = initialOptions;
        this.selectColumn = $(selectColumnSelector);
        this.selectedColumnsWrapper = $(selectedColumnsWrapperSelector);
        this.selectedItems = [];

        this.selectColumn.on("change", (e)=>{
            let selectedOption = this.options.find((o)=>{
                if(o.value == this.selectColumn.val()){
                    return o;
                }
            });
            this.selectedItems.push(selectedOption);
            this.render();
        });

        this.render();

    }

    render(){
        this.selectColumn.empty();

        let remainingOptions = this.options.filter((o)=>{
            let selectedOption = this.selectedItems.find((s)=>{
                if(s.value == o.value){
                    return s;
                }
            });
            return selectedOption == undefined;
        });

        this.selectColumn.append(`<option value="" disabled selected>Pick one</option>`);
        remainingOptions.forEach((o)=>{
            this.selectColumn.append(`<option value="${o.value}">${o.text}</option>`);
        });
        this.selectedColumnsWrapper.empty();
        if(this.selectedItems.length > 0){
            this.selectedItems.forEach((s)=>{
                let button = this._createElementFromHTML(`
                    <button class="column-item-assigned-remove-button">
                        x
                    </button>
                `);
                $(button).on('click', ()=>{
                    this.unselectElementByValue(s.value);
                });
                let el = this._createElementFromHTML(`
                    <div class="column-item-assigned">
                        <span>${s.text}</span>
                    </div>
                `);
                $(el).prepend(button);
                this.selectedColumnsWrapper.append($(el));
            });
        }else{
            this.selectedColumnsWrapper.append(`<div class="text-secondary" style="border-radius: 5px; font-size:0.8em; background-color: rgba(0,0,0,0.05); padding: 10px; width: 100%">None selected</div>`);
        }

    }

    unselectElementByValue(value){
        if(value){
            let newSelected = this.selectedItems.filter((s)=>{
                return s.value != value;
            });
            this.selectedItems = newSelected;
            this.render();
        }
    }

    selected(){
        let selectedElements = this.selectedItems.map((s)=>{
            return s.value;
        });
        return selectedElements;
    }

    selectedRaw(){
        return this.selectedItems;
    }

    _createElementFromHTML(htmlString) {
		var div = document.createElement('div');
		div.innerHTML = htmlString.trim();
		// Change this to div.childNodes to support multiple top-level nodes.
		return div.firstChild;
	}
}

export default DynamicOptionsPicker;
