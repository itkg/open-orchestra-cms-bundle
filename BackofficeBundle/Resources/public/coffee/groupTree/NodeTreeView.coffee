NodeTreeView = OrchestraView.extend(
  events:
    'click em.fa': 'toggleItemDisplay'
  initialize: (options) ->
    @initializer options
    @loadTemplates [
      'OpenOrchestraBackofficeBundle:BackOffice:Underscore/groupTree/nodeTree',
      'OpenOrchestraBackofficeBundle:BackOffice:Underscore/backToList',
    ]
    return

  initializer: (options) ->
    @options = options
    @options.listUrl = appRouter.generateUrl('listEntities', entityType: options.entityType) if options.listUrl == undefined
    @options.formView = 'editEntityTab'
    @options.domContainer = @$el

  render: ->
    @options.domContainer.html @renderTemplate('OpenOrchestraBackofficeBundle:BackOffice:Underscore/groupTree/nodeTree')
    @options.domContainer.append @renderTemplate('OpenOrchestraBackofficeBundle:BackOffice:Underscore/backToList',
      listUrl : @options.listUrl
    )
    currentView = @
    $.ajax
      url: currentView.options.html.links._self_node_tree
      method: "GET"
      success: (response) ->
        currentView.options.nodes = response
        $.ajax
          url: currentView.options.html.links._role_list_node
          method: "GET"
          success: (response) ->
            currentView.options.roles = response
            currentView.renderHead()
            currentView.renderTreeElement()

  renderTreeElement: ->
    subContainer = @options.domContainer.find('ul').first()
    nodeTreeElementViewClass = appConfigurationView.getConfiguration('group_tab_node_tree_element', 'editEntityTab')
    new nodeTreeElementViewClass(
      group: @options.html
      nodes: @options.nodes
      domContainer: subContainer
      roles: @options.roles
    )
    $('.fa', @$el).addClass 'fa-minus-square-o'

  renderHead: ->
    for role in @options.roles.roles
      @options.domContainer.find('.head-element').first().append '<div class="col-lg-2">' + role.description + '</div>'

  toggleItemDisplay: (e) ->
    OpenOrchestra.toggleTreeNodeDisplay e
)

jQuery ->
  appConfigurationView.setConfiguration 'group_tab_node_tree', 'editEntityTab', NodeTreeView
