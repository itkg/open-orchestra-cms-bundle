import AbstractDataTableView from '../../../Service/DataTable/View/AbstractDataTableView'

/**
 * @class GroupListForUserView
 */
class GroupListForUserView extends AbstractDataTableView
{
    /**
     * @inheritDoc
     */
    preinitialize(options) {
        super.preinitialize(options);
    }

    /**
     * @inheritDoc
     */
    getTableId() {
        return 'group_list';
    }

    /**
     * @inheritDoc
     */
    getColumnsDefinition() {
        return [
            {
                name: 'checkbox',
                orderable: false,
                createdCell: this._addCheckbox
            },
            {
                name: "label",
                title: Translator.trans('open_orchestra_backoffice.table.group.label'),
                orderable: true,
                orderDirection: 'desc',
                visibile: true
            },
            {
                name: "site.name",
                title: Translator.trans('open_orchestra_backoffice.table.group.site_name'),
                orderable: true,
                orderDirection: 'desc',
                visibile: true
            }
        ];
    }

    /**
     *
     * @param {Object} td
     *
     * @private
     */
    _addCheckbox(td, cellData, rowData) {
        let id = rowData.get('id')
        let cell = '' +
            '<div class="form-group ">' +
            '    <div class="switch-button">' +
            '        <span>' + Translator.trans('open_orchestra_backoffice.form.swchoff.off') + '</span>' +
            '        <label class="switch" for="group[' + id + ']">' +
            '            <input type="checkbox" value="' + id + '" name="group" id="group[' + id + ']">' +
            '            <div class="slider"></div>' +
            '        </label>' +
            '        <span>' + Translator.trans('open_orchestra_backoffice.form.swchoff.on') + '</span>' +
            '    </div>' +
            '</div>';
        $(td).html(cell);
    }
}

export default GroupListForUserView;
