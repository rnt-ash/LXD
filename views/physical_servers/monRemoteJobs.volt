<table class="table table-condensed table-striped">
    <thead>
        <tr>
            <th colspan="4">
                <div class="pull-left">
                    <h5 class="panel-title pull-left">
                        <a href="#monRemoteJobs{{item.id}}" onclick="toggleIcon('#monRemoteJobsToggleIcon'+{{item.id}})" data-toggle="collapse" class="pull-left">
                            <i id="monRemoteJobsToggleIcon{{item.id}}" class="fa fa-chevron-right"></i>&nbsp;{{ _("monitoring_monremotejobs_title") }}
                        </a>
                    </h5>
                </div>
                <div class="pull-right">
                    <div class="btn-group">
                    {{ link_to("physical_servers/monRemoteJobAdd/"~item.id,'<i class="fa fa-plus"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monremotejobs_add")) }}
                    </div>
                </div>
            </th>
        </tr>
    </thead>
    <tbody><tr><td class="removePadding">
        <div id="monRemoteJobs{{item.id}}" class="collapse">
            <table class="table table-condensed table-striped sub-table">
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
                                {{ link_to("physical_servers/monRemoteJobEdit/"~monRemoteJob.id,'<i class="fa fa-pencil"></i>',
                                    'class': 'btn btn-default btn-xs disabled', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monremotejobs_edit")) }}
                                <a href="#" link="/physical_servers/monRemoteJobDelete/{{monRemoteJob.id}}" text="{{ _("monitoring_monremotejobs_deleteconf") }}"
                                    class="btn btn-default btn-xs confirm-button disabled" data-toggle="tooltip" data-placement="top" title="{{ _("monitoring_monremotejobs_deleteconf") }}"><i class="fa fa-trash-o"></i></a>
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
            </table>
        </div>
    </td></tr></tbody>
</table>
