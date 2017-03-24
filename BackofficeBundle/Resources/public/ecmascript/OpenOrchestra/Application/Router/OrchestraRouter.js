import LoaderView        from '../View/Loader/LoaderView'
import NavigationManager from '../../Service/NavigationManager'

/**
 * @class OrchestraRouter
 */
class OrchestraRouter extends Backbone.Router
{
    /**
     * @inheritdoc
     */
    route(route, name, callback) {
        super.route(route, name, callback);
        Backbone.history.addRoutePattern(name, route);
    }

    /**
     * @param {Object} $region - Jquery selector
     * @private
     */
    _displayLoader($region) {
        let loaderView = new LoaderView();
        $region.html(loaderView.$el);
    }

    /**
     * @param {Function} callback
     * @param {Object}   args
     * @param {string}   name
     */
    execute(callback, args, name) {
        super.execute(callback, args, name);
        let items = this.getBreadcrumb();
        this._updateBreadcrumb(items);
        this._highlight(name);
    }

    /**
     * @returns {Array}
     * @private
     */
    getBreadcrumb() {
        return [];
    }

    /**
     * @returns {Object}
     * @private
     */
    getMenuHighlight() {
        return {};
    }

    /**
     * @returns {Object}
     * @private
     */
    getBreadcrumbHighlight() {
        return {};
    }

    /**
     * @param {Array} items
     * @private
     */
    _updateBreadcrumb(items) {
        NavigationManager.updateBreadcrumb(items);
    }

    /**
     * @param {string} name
     * @private
     */
    _highlight(name) {
        let breadcrumb = this.getBreadcrumbHighlight();
        let menu = this.getMenuHighlight();

        if (breadcrumb !== null) {
            if (breadcrumb.hasOwnProperty(name)) {
                NavigationManager.highlightBreadcrumb(breadcrumb[name]);
            } else if (breadcrumb.hasOwnProperty('*')) {
                NavigationManager.highlightBreadcrumb(breadcrumb['*']);
            }
        }

        if (menu !== null) {
            if (menu.hasOwnProperty(name)) {
                NavigationManager.highlightMenu(menu[name]);
            } else if (menu.hasOwnProperty('*')) {
                NavigationManager.highlightMenu(menu['*']);
            }

        }
    }
}

export default OrchestraRouter;
