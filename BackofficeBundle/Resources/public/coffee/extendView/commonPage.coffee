extendView = extendView || {}
extendView['commonPage'] = {
  events:
    'click i.show-areas': 'showAreas'
    'click i.hide-areas': 'hideAreas'

  showAreas: ->
    $('.show-areas').hide()
    $('.hide-areas').show()
    $('.area-toolbar').addClass('shown')

  hideAreas: ->
    $('.hide-areas').hide()
    $('.show-areas').show()
    $('.area-toolbar').removeClass('shown')

  addConfigurationButton: () ->
    pageConfigurationButtonViewClass = appConfigurationView.getConfiguration(@options.entityType, 'addConfigurationButton')
    new pageConfigurationButtonViewClass(@addOption(
      viewContainer: @
    ))

}
