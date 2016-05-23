###*
 * @namespace OpenOrchestra
###
window.OpenOrchestra or= {}

###*
 * @class ContentTypeFormView
###
class OpenOrchestra.ContentTypeFormView extends FullPageFormView

  events:
    'change .content_type_change_type': 'changeContentTypeChange'

  ###*
   * Refresh the navigation when a content type is created
  ###
  onElementCreated: ->
    displayRoute = appRouter.generateUrl('listEntities', entityType: @options.entityType)
    refreshMenu(displayRoute, true)

  ###*
   * Refresh a field form setting when the type is changed
   *
   * @param {Object} event
  ###
  changeContentTypeChange: (event) ->
    event.preventDefault()
    viewContext = @
    targetId = $(event.currentTarget).attr('id')
    containerId = targetId.replace(/_type$/g, '')
    displayLoader('#' + containerId + '_options')
    form = $('[for="' + containerId + '_default_value"]', @$el)
    form.parent().remove()
    $('form', @$el).ajaxSubmit
      url: form.data('action')
      type: 'PATCH'
      success: (response) ->
        $('#' + containerId, viewContext.$el).html $('#' + containerId, response).html()
        activateForm(viewContext, $('#' + containerId, viewContext.$el))

jQuery ->
  appConfigurationView.setConfiguration('content_types', 'editEntity', OpenOrchestra.ContentTypeFormView)
  appConfigurationView.setConfiguration('content_types', 'addEntity', OpenOrchestra.ContentTypeFormView)
