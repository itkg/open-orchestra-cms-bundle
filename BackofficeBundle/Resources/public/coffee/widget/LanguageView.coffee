LanguageView = AbstractConcurrancyCounter.extend(
  events:
    'click a.change-language': 'changeLanguage'

  initialize: (options) ->
    @options = options
    @loadTemplates [
      "OpenOrchestraBackofficeBundle:BackOffice:Underscore/widgetLanguage"
    ]
    return

  render: ->
    @setElement @renderTemplate('OpenOrchestraBackofficeBundle:BackOffice:Underscore/widgetLanguage',
      language: @options.language
      currentLanguage: @options.currentLanguage.language
    )
    @options.domContainer.append @$el
    return

  changeLanguage: (event) ->
    event.preventDefault()
    displayLoader()
    redirectUrl = appRouter.generateUrl(@options.currentLanguage.path, appRouter.addParametersToRoute(
      language: $(event.currentTarget).data('language')
      sourceLanguage: @options.currentLanguage.language
    ))
    Backbone.history.navigate(redirectUrl, {trigger: true})
)
