<div class="clearfix panel panel-default sub-panel">
    <div class="panel-heading">
        <span role="button" data-target="#monJobs{{item.id}}" onclick="toggleIcon('#monJobsToggleIcon'+{{item.id}})" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="monJobsToggleIcon{{item.id}}" class="fa fa-chevron-right"></i>&nbsp;{{ _("monitoring_monjobs_title") }}
                <div class="pull-right">
                    <div class="btn-group">
                    {{ link_to("virtual_servers/monJobsAdd/"~item.id,'<i class="fa fa-plus"></i>',
                        'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monjobs_add")) }}
                    </div>
                </div>
            </h5>
        </span>
    </div>
    <div id="monJobs{{item.id}}" class="panel-collapse collapse">
        <table class="table table-striped table-condensed table-hover col-md-6 col-xs-12 ">
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
                                'class': 'btn btn-default btn-xs disabled pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monlocaljobs_edit")) }}
                            {{ link_to("virtual_servers/monLocalJobDiagram/"~monLocalJob.id,'<i class="fa fa-pie-chart"></i>',
                                'class': 'btn btn-default btn-xs disabled pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monlocaljobs_diagram")) }}
                            <a href="#" link="/virtual_servers/monLocalJobDelete/{{monLocalJob.id}}" text="{{ _("monitoring_monlocaljobs_deleteconf") }}"
                                class="btn btn-default btn-xs confirm-button disabled pending" data-toggle="tooltip" data-placement="top" title="{{ _("monitoring_monlocaljobs_deleteconf") }}"><i class="fa fa-trash-o"></i></a>
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
        <table class="table table-striped table-condensed table-hover col-md-6 col-xs-12 ">
            <tbody>
            {% if item.getMonRemoteJobs().count() == 0 %}
                <tr colspan="4">
                    <td>
                        {{ _("monitoring_monremotejobs_notfound") }}
                    </td>
                </tr>
            {% else %}
                <tr>
                    <th>Actions</th>
                    <th>Behavior</th>
                    <th>Uptime</th>
                    <th>Status</th>
                </tr>
                {% for monRemoteJob in item.getMonRemoteJobs() %}
                    <tr>
                        <td>
                            {{ link_to("virtual_servers/monRemoteJobEdit/"~monRemoteJob.id,'<i class="fa fa-pencil"></i>',
                                'class': 'btn btn-default btn-xs disabled pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monremotejobs_edit")) }}
                            <a href="#" link="/virtual_servers/monRemoteJobDelete/{{monRemoteJob.id}}" text="{{ _("monitoring_monremotejobs_deleteconf") }}"
                                class="btn btn-default btn-xs confirm-button disabled pending" data-toggle="tooltip" data-placement="top" title="{{ _("monitoring_monremotejobs_deleteconf") }}"><i class="fa fa-trash-o"></i></a>
                        </td>
                        <td>
                            {{ monRemoteJob.getMonBehaviorClass() }}
                        </td>
                        <td>
                            {% if monRemoteJob.getUptime() != '' %}
                                {% set uptime = monRemoteJob.getUptime()|json_decode %}
                                {{ uptime.everuppercentage * 100 }}%
                            {% else %}
                                {{ _("monitoring_monremotejobs_no_uptime") }}
                            {% endif %}
                        </td>
                        <td>
                            {{ monRemoteJob.getStatus() }}
                        </td>
                    </tr>
                {% endfor %}
            {% endif %}
            </tbody>
        </table>
    </div>
</div>