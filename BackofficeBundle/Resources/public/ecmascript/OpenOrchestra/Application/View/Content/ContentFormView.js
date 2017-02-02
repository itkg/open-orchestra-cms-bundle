import AbstractFormView     from '../../../Service/Form/View/AbstractFormView'
import Application          from '../../Application'
import ApplicationError     from '../../../Service/Error/ApplicationError'
import Content              from '../../Model/Content/Content'
import Contents             from '../../Collection/Content/Contents'
import FormViewButtonsMixin from '../../../Service/Form/Mixin/FormViewButtonsMixin'
import ContentToolbarView   from './ContentToolbarView'
import ContentVersionsView  from './ContentVersionsView'

/**
 * @class ContentFormView
 */
class ContentFormView extends mix(AbstractFormView).with(FormViewButtonsMixin) 
{
    /**
     * Pre initialize
     * @param {Object} options
     */
    preinitialize(options) {
        super.preinitialize(options);
        this.events['change #oo_content_status'] = this._toggleCheckboxSaveOldPublishedVersion;
    }

    /**
     * Initialize
     * @param {Form}   form
     * @param {String} name
     * @param {String} contentTypeId
     * @param {String} language
     * @param {Array}  siteLanguageUrl
     * @param {String} contentId
     * @param {String} version
     */
    initialize({form, name, contentTypeId, language, siteLanguageUrl, contentId, version}) {
        super.initialize({form : form});
        this._name = name;
        this._contentTypeId = contentTypeId;
        this._language = language;
        this._siteLanguageUrl = siteLanguageUrl;
        this._contentId = contentId;
        this._version = version;
    }

    /**
     * @inheritdoc
     */
    render() {
        let template = this._renderTemplate('Content/contentEditView', {
            contentTypeId: this._contentTypeId,
            language: this._language,
            name: this._name,
            siteLanguageUrl: this._siteLanguageUrl
        });
        this.$el.html(template);
        this._$formRegion = $('.form-edit', this.$el);
        super.render();

        return this;
    }

    /**
     * @inheritDoc
     */
    _renderForm() {
        this._renderContentActionToolbar($('.content-action-toolbar', this.$el));
        super._renderForm();
        // hide checkbox oo_content_saveOldPublishedVersion by default
        $('#oo_content_saveOldPublishedVersion', this.$el).closest('.form-group').hide();
    }

    /**
     * @param {Object} $selector
     * @private
     */
    _renderContentActionToolbar($selector) {
        this._displayLoader($selector);
        new Contents().fetch({
            apiContext: 'list-version',
            urlParameter: {
                language: this._language,
                contentId: this._contentId
            },
            success: (contentVersions) => {
                let contentToolbarView = new ContentToolbarView({
                        contentVersions: contentVersions,
                        name: this._name,
                        version: this._version,
                        contentTypeId: this._contentTypeId,
                        language: this._language,
                        contentId: this._contentId,
                        contentFormView: this
                });
                contentToolbarView.listenTo(this, 'show.new_version.form', contentToolbarView.newVersionForm);
                $selector.html(contentToolbarView.render().$el);
            }
        })
    }

    /**
     * Manage Version
     * @param {Contents} contentVersions
     */
    manageVersion(contentVersions) {
        let contentVersionsView = new ContentVersionsView({
            collection: contentVersions,
            contentId: this._contentId,
            language: this._language
        });
        this._$formRegion.html(contentVersionsView.render().$el);
    }

    /**
     * @private
     */
    _toggleCheckboxSaveOldPublishedVersion(event) {
        let formGroupCheckbox = $('#oo_content_saveOldPublishedVersion', this.$el).closest('.form-group');
        formGroupCheckbox.hide();
        if ($('option:selected', $(event.currentTarget)).attr('data-published-state')) {
            formGroupCheckbox.show();
        }
    }

    /**
     * Delete
     * @param {event} event
     */
    _deleteElement(event) {
        let content = new Content({'id': this._contentId});
        let contentTypeId = this._contentTypeId;
        let language = this._language;

        content.destroy({
            apiContext: 'delete-multiple',
            success: () => {
                let url = Backbone.history.generateUrl('listContent', {
                    contentTypeId: contentTypeId,
                    language: language
                });
                Backbone.history.navigate(url, true);
            }
        });
    }
}

export default ContentFormView;
