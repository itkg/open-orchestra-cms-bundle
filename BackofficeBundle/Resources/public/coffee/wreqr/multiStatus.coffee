widgetChannel.commands.setHandler 'initMultiStatus', (view) ->
  view.events['click .change-status-' + view.cid] = 'changeStatus'
  view.changeStatus = (event) ->
    url = $(event.currentTarget).data("url")
    statusId = $(event.currentTarget).data("status")
    displayLoader()
    data =
      status_id: statusId
    data = JSON.stringify(data)
    $.post(url, data).always (response) ->
      Backbone.history.loadUrl(Backbone.history.fragment)
      return
    return

widgetChannel.reqres.setHandler 'initMultiStatus', ->
  return ['widgetStatus']

widgetChannel.commands.setHandler 'addMultiStatus', (view) ->
    $.ajax
      type: "GET"
      data:
        language: view.options.multiStatus.language
        version: view.options.multiStatus.version
      url: view.options.multiStatus.status_list
      success: (response) ->
        widgetStatus = view.renderTemplate('widgetStatus',
          current_status: view.options.multiStatus.status
          statuses: response.statuses
          status_change_link: view.options.multiStatus.self_status_change
          cid: view.cid
        )
        addCustomJarvisWidget(widgetStatus)
        return
