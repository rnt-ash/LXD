<div class="clearfix panel panel-default sub-panel">
    <div class="panel-heading">
        <span role="button" data-target="#monLocalJobs{{item.id}}" onclick="toggleIcon('#monLocalJobsToggleIcon'+{{item.id}})" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="monLocalJobsToggleIcon{{item.id}}" class="fa fa-chevron-right"></i>&nbsp;{{ _("monitoring_monlocaljobs_title") }}
                <div class="pull-right">
                    <div class="btn-group">
                    {{ link_to("physical_servers/monLocalJobAdd/"~item.id,'<i class="fa fa-plus"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monlocaljobs_add"),'onclick':'event.stopPropagation();') }}
                    </div>
                </div>
            </h5>
        </span>
    </div>
    <div id="monLocalJobs{{item.id}}" class="panel-collapse collapse">
        <table class="table table-condensed table-striped table-hover collapse-table">
            {% if item.getMonLocalJobs().count() == 0 %}
                <tr colspan="4">
                    <td>
                        {{ _("monitoring_monlocaljobs_notfound") }}
                    </td>
                </tr>
            {% else %}
                <tr>
                    <th>Actions</th>
                    <th>Behavior</th>
                    <th>Status</th>
                </tr>
                {% for monLocalJob in item.getMonLocalJobs() %}
                    <tr>
                        <td>
                            {{ link_to("physical_servers/monLocalJobEdit/"~monLocalJob.id,'<i class="fa fa-pencil"></i>',
                                'class': 'btn btn-default btn-xs disabled', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monlocaljobs_edit")) }}
                            {{ link_to("physical_servers/monLocalJobDiagram/"~monLocalJob.id,'<i class="fa fa-pie-chart"></i>',
                                'class': 'btn btn-default btn-xs disabled', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monlocaljobs_diagram")) }}
                            <a href="#" link="/physical_servers/monLocalJobDelete/{{monLocalJob.id}}" text="{{ _("monitoring_monlocaljobs_deleteconf") }}"
                                class="btn btn-default btn-xs confirm-button disabled" data-toggle="tooltip" data-placement="top" title="{{ _("monitoring_monlocaljobs_deleteconf") }}"><i class="fa fa-trash-o"></i></a>
                        </td>
                        <td>
                            {{ monLocalJob.getMonBehaviorClass() }}
                        </td>
                        <td>
                            {{ monLocalJob.getStatus() }}
                        </td>
                    </tr>
                {% endfor %}
            {% endif %}
        </table>
    </div>
</div>