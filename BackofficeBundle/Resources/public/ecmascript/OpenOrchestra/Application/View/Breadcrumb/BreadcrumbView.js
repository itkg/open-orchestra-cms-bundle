import OrchestraView from '../OrchestraView'

/**
 * @class BreadcrumbView
 */
class BreadcrumbView extends OrchestraView
{
    /**
     * @param options
     */
    constructor(options) {
        super(options);
        this._breadcrumb = []
    }

    /**
     * Render error
     */
    render() {
        let template = this._renderTemplate('Breadcrumb/breadcrumbView', {
            breadcrumb: this._breadcrumb,
            currentFragment: '#' + Backbone.history.fragment
        });
        this.$el.html(template);

        return this;
    }

    /**
     * @param {Array} items
     */
    setItems(items) {
        this._breadcrumb = items;
    }
}

export default BreadcrumbView;
