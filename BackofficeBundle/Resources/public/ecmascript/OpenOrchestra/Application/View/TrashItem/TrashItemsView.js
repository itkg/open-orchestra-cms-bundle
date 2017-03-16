import AbstractCollectionView  from '../../../Service/DataTable/View/AbstractCollectionView'
import Application             from '../../Application'
import TrashItemListView       from './TrashItemListView'
import DatePicker              from '../../../Service/Form/Behavior/DatePicker'

/**
 * @class TrashItemsView
 */
class TrashItemsView extends AbstractCollectionView
{
    /**
     * Render trash items view
     */
    render() {
        let template = this._renderTemplate('TrashItem/trashItemsView', {
            filterType : Application.getConfiguration().getParameter('trash_item_type')
        });
        this.$el.html(template);
        this._listView = new TrashItemListView({
            collection: this._collection,
            settings: this._settings
        });
        $('.trash-item-list', this.$el).html(this._listView.render().$el);
        DatePicker.activate($('.datepicker', this.$el));

        return this;
    }
}

export default TrashItemsView;
