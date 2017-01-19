import AbstractCellFormatter from './AbstractCellFormatter'

/**
 * @class TextFormatter
 */
class TextFormatter extends AbstractCellFormatter
{
    /**
     * test if field is supported
     *
     * @param {Object} field
     */
    support(field) {
        return field.type == 'text';
    }

    /**
     * render the field
     *
     * @param {Object} field
     */
    format(field) {
        return function(td, cellData, rowData) {
            if (cellData.length > 20) {
                cellData = cellData.substr(0, 17) + '...';
            }
            $(td).html(cellData)
        };
    }
}

// unique instance of TextFormatter
export default (new TextFormatter);
