OrchestraViewConfigurator = ->
  configurations: {}
  baseConfigurations:
    'editEntity': FullPageFormView
    'addEntity': FullPageFormView
    'editEntityTab': TabElementFormView
    'showTab': TabView
    'addArea': AreaView
    'addAreaFlex': OpenOrchestra.AreaFlex.AreaFlexView
    'showAreaFlexToolbar': OpenOrchestra.AreaFlex.AreaFlexToolbarView
    'addBlock': BlockView
    'addButtonAction': TableviewAction
    'addConfigurationButton': PageConfigurationButtonView
    'showTableCollection': TableviewCollectionView
    'showTableHeader': DataTableViewSearchHeader
    'addDataTable': DataTableView
    'showAdminForm': AdminFormView
    'showBlocksPanel': BlocksPanelView
    'showNode': NodeView
    'showTemplate': TemplateView
    'showTemplateFlex': OpenOrchestra.TemplateFlex.TemplateFlexView
    'showLanguage': LanguageView
    'showDuplicate': DuplicateView
    'showPreviewLinks': PreviewLinkView
    'showStatus': StatusView
    'showVersion': VersionView
    'showVersionSelect': VersionSelectView
    'showOrchestraModal': OrchestraModalView
    'addFieldOptionDefaultValue': FieldOptionDefaultValueView
    'showFlashBag': FlashBagView
    'apiError': DisplayApiErrorView

  setConfiguration: (entityType, action, view) ->
    @configurations[entityType] = [] if typeof @configurations[entityType] == "undefined"
    @configurations[entityType][action] = view
    return

  getConfiguration: (entityType, action) ->
    entityTypeConfiguration = @configurations[entityType]
    if typeof entityTypeConfiguration != 'undefined'
      view = entityTypeConfiguration[action]
      if typeof view != 'undefined'
        return view
    return @baseConfigurations[action]

jQuery ->
  window.appConfigurationView = new OrchestraViewConfigurator()
