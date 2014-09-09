TableviewCollectionView = Backbone.View.extend(
  el: '#content'
  events:
    'click #none': 'clickAdd'
  initialize: (options) ->
    @elements = options.elements
    @displayedElements = options.displayedElements
    @title = options.title
    @listUrl = options.listUrl
    key = 'click a.ajax-add-' + @cid
    @events[key] = 'clickAdd'
    _.bindAll this, "render"
    @elementsTemplate = _.template($('#tableviewCollectionView').html())
    @render()
    return
  render: ->
    $(@el).html @elementsTemplate (
      displayedElements: @displayedElements
      links: @elements.get('links')
      cid: @cid
    )
    $('.js-widget-title', @$el).text @title
    for element of @elements.get(@elements.get('collection_name'))
      @addElementToView (@elements.get(@elements.get('collection_name'))[element])
    $('#tableviewCollectionTable').dataTable(
      searching: false
      ordering: true
      lengthChange: false
    )
    return
  addElementToView: (elementData) ->
    elementModel = new TableviewModel
    elementModel.set elementData
    view = new TableviewView(
      element: elementModel
      displayedElements: @displayedElements
      title: @title
      listUrl: @listUrl
    )
    this.$el.find('tbody').append view.render().el
    return
  clickAdd: (event) ->
    event.preventDefault()
    title = @title
    listUrl = @listUrl
    displayedElements = @displayedElements
    $.ajax
      url: @elements.get('links')._self_add
      method: 'GET'
      success: (response) ->
        view = new FullPageFormView(
          html: response
          title: title
          listUrl: listUrl
          displayedElements: displayedElements
        )
)
