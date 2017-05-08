<table class="table table-condensed table-striped">
    <thead>
        <tr>
            <th colspan="4">
                <div class="pull-left">
                    <h5 class="panel-title pull-left">
                        <a href="#monLocalJobs{{item.id}}" onclick="toggleIcon('#monLocalJobsToggleIcon'+{{item.id}})" data-toggle="collapse" class="pull-left">
                            <i id="monLocalJobsToggleIcon{{item.id}}" class="fa fa-chevron-right"></i>&nbsp;{{ _("monitoring_monlocaljobs_title") }}
                        </a>
                    </h5>
                </div>
                <div class="pull-right">
                    <div class="btn-group">
                    {{ link_to("physical_servers/monLocalJobAdd/"~item.id,'<i class="fa fa-plus"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monlocaljobs_add")) }}
                    </div>
                </div>
            </th>
        </tr>
    </thead>
    <tbody><tr><td class="removePadding">
        <div id="monLocalJobs{{item.id}}" class="collapse">
            <table class="table table-condensed table-striped sub-table">
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
    </td></tr></tbody>
</table>
