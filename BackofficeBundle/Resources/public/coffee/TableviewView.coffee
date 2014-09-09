TableviewView = Backbone.View.extend(
  tagName: 'tr'
  events:
    'click a.ajax-delete': 'clickDelete'
    'click a.ajax-edit': 'clickEdit'
  initialize: (options) ->
    @element = options.element
    @displayedElements = options.displayedElements
    @title = options.title
    _.bindAll this, "render"
    @elementTemplate = _.template($('#tableviewView').html())
    @actionsTemplate = _.template($('#tableviewActions').html())
    return
  render: ->
    for displayedElement in @displayedElements
      $(@el).append @elementTemplate(
        value: @element.get(displayedElement)
      )
    $(@el).append @actionsTemplate(
      links: @element.get('links')
    )
    this
  clickDelete: (event) ->
    event.preventDefault()
    if confirm('Delete this element ?')
      $.ajax
        url: @element.get('links')._self_delete
        method: 'DELETE'
        success: (response) ->
          return
      @$el.hide()
  clickEdit: (event) ->
    event.preventDefault()
    title = @title
    $('.modal-title').text 'Edit'
    $.ajax
      url: @element.get('links')._self_form
      method: 'GET'
      success: (response) ->
        view = new FullPageFormView(
          html: response
          title: title
        )
)
