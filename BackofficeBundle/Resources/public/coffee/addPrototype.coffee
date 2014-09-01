PO = PO or {}
PO.formPrototypes =
  addButtonContainer: "<li class=\"prototype-add-container\"></li>"
  addButton: "<button class=\"button-small prototype-add\">__label__</button>"
  removeButton: "<button class=\"button-small prototype-remove\">__label__</button>"
  prototypes: []
  addPrototype: (collectionHolder) ->
    @prototypes.push new PO.formPrototype(collectionHolder)
    return

PO.formPrototype = (collectionHolder) ->
  @collectionHolder = collectionHolder
  @:: = @collectionHolder.data("prototype")
  @index = @collectionHolder.find("input").length
  @limit = @collectionHolder.data("limit")
  @addButton = $(PO.formPrototypes.addButton.replace(/__label__/g, @collectionHolder.data("prototype-label-add")))
  @removeButton = $(PO.formPrototypes.removeButton.replace(/__label__/g, @collectionHolder.data("prototype-label-remove")))
  @newElementLabel = @collectionHolder.data("prototype-label-new")
  @addButtonContainer = $(PO.formPrototypes.addButtonContainer).append(@addButton)
  @addButtonExist = false
  self = this

  # add old class for know if this children is already save in database
  @collectionHolder.children().each ->
    prototype = self.createRemoveButton($(this))
    if prototype.find(".alert-error").length is 0
      prototype.addClass("old").removeClass("new")
    else
      prototype.addClass "error"
    return

  @toogleAddButton()
  return

PO.formPrototype:: =
  toogleAddButton: ->
    if @limit is `undefined` or @collectionHolder.find("input").length < @limit
      @createAddButton()
    else
      @removeAddButton()
    return

  createAddButton: ->
    self = this
    unless @addButtonExist
      @collectionHolder.append @addButtonContainer
      @addButtonExist = not @addButtonExist

      # add event in add button
      @addButton.on "click", (e) ->
        e.preventDefault()
        self.addPrototype()
        self.clickLastPrototype()
        return

    return

  removeAddButton: ->
    if @addButtonExist
      @collectionHolder.children(".prototype-add-container").remove()
      @addButtonExist = not @addButtonExist
    return

  createRemoveButton: (prototype) ->
    self = this
    newRemoveButton = self.removeButton.clone()
    prototype.append newRemoveButton
    newRemoveButton.on "click", (e) ->
      e.preventDefault()
      self.removePrototype $(this)
      return

    prototype

  clickLastPrototype: ->
    @collectionHolder.children().not(".prototype-add-container").last().find("input").click()
    return

  addPrototype: ->
    newPrototype = @::replace(/__name__label__/g, @newElementLabel)
    newPrototype = newPrototype.replace(/__name__/g, @index)

    # increase the index with one for the next item
    @index++

    # Display the input in the page before the add button
    @addButtonContainer.before newPrototype
    @createRemoveButton @addButtonContainer.prev()
    @toogleAddButton()
    return

  removePrototype: (removeButton) ->
    removeButton.parent().remove()
    @toogleAddButton()
    return
