<table class="table">
    <thead>
        <tr>
            <th colspan="4">
                <div class="pull-left"><h5 class="panel-title pull-left">{{ _("monitoring_monlocaljobs_title") }}</h5></div>
                <div class="pull-right">
                    <div class="btn-group">
                    {{ link_to("virtual_servers/monLocalJobAdd/"~item.id,'<i class="fa fa-plus"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monlocaljobs_add")) }}
                    </div>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
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
                    {{ link_to("virtual_servers/monLocalJobEdit/"~monLocalJob.id,'<i class="fa fa-pencil"></i>',
                        'class': 'btn btn-default btn-xs disabled', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monlocaljobs_edit")) }}
                    {{ link_to("virtual_servers/monLocalJobDiagram/"~monLocalJob.id,'<i class="fa fa-pie-chart"></i>',
                        'class': 'btn btn-default btn-xs disabled', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monlocaljobs_diagram")) }}
                    <a href="#" link="/virtual_servers/monLocalJobDelete/{{monLocalJob.id}}" text="{{ _("monitoring_monlocaljobs_deleteconf") }}"
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
    </tbody>
</table>
