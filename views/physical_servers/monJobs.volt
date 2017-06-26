{# check if any MonJob is not up or has not status normal #}
{% set ok = true %}
{% set nokMonJobsCount = 0 %}
{% for monLocalJob in item.getMonLocalJobs() %}
    {% if monLocalJob.getStatus() != 'normal' %}
        {% set nokMonJobsCount += 1 %}
        {% set ok = false %}
    {% endif %}
{% endfor %}
{% for monRemoteJob in item.getMonRemoteJobs() %}
    {% if monRemoteJob.getStatus() != 'up' %}
        {% set nokMonJobsCount += 1 %}
        {% set ok = false %}
    {% endif %}
{% endfor %}

<div class="clearfix panel panel-{% if ok == true %}default{% else %}warning{% endif %} sub-panel">
    <div class="panel-heading">
        <span role="button" data-target="#monJobs{{item.id}}" onclick="toggleIcon('#monJobsToggleIcon'+{{item.id}})" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="monJobsToggleIcon{{item.id}}" class="fa fa-chevron-right"></i>&nbsp;{{ _("monitoring_monjobs_title") }} {% if ok == false %}<i class="fa fa-exclamation-circle"></i> <span class="small">{{ nokMonJobsCount }} {{ _("monitoring_monjobs_nok_title") }}</span>{% endif %}
                <div class="pull-right">
                    <div class="btn-group">
                    {{ link_to("physical_servers/monJobsAdd/"~item.id,'<i class="fa fa-plus"></i>',
                        'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monjobs_add")) }}
                    </div>
                </div>
            </h5>
        </span>
    </div>
    <div id="monJobs{{item.id}}" class="panel-collapse collapse">
        <div class="col-md-6 col-xs-12 removePadding">
            <table class="table table-striped table-condensed table-hover panel-sub-table">
                <tbody>
                    <tr>
                        <th colspan="4">Local</th>
                    </tr>
                {% if item.getMonLocalJobs().count() == 0 %}
                    <tr colspan="4">
                        <td>
                            {{ _("monitoring_monlocaljobs_notfound") }}
                        </td>
                    </tr>
                {% else %}
                    {% for monLocalJob in item.getMonLocalJobs() %}
                        {% set statusClass = '' %}
                        {% if monLocalJob.getStatus() == 'warning' %}
                            {% set statusClass = 'warning' %}
                        {% elseif monLocalJob.getStatus() == 'maximal' %}
                            {% set statusClass = 'danger' %}
                        {% endif %}
                        <tr class="{{ statusClass }}">
                            <td class="tableBtns">
                                {{ link_to("physical_servers/monJobsEdit/"~monLocalJob.id,'<i class="fa fa-pencil"></i>',
                                    'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monjobs_edit")) }}
                                {{ link_to("physical_servers/monJobsDiagram/"~monLocalJob.id,'<i class="fa fa-pie-chart"></i>',
                                    'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monjobs_diagram")) }}
                                {% if monLocalJob.getMuted() == 0 %}
                                    <a href="#/" link="/physical_servers/monJobsMute/{{monLocalJob.id}}" text="{{ _("monitoring_monjobs_muteconf") }}"
                                        class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" title="{{ _("monitoring_monjobs_mute") }}"><i class="fa fa-volume-off"></i></a>
                                {% else %}
                                    <a href="#/" link="/physical_servers/monJobsMute/{{monLocalJob.id}}" text="{{ _("monitoring_monjobs_unmuteconf") }}"
                                        class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" title="{{ _("monitoring_monjobs_unmute") }}"><i class="fa fa-volume-up"></i></a>
                                {% endif %}
                                <a href="#/" link="/physical_servers/monJobsDelete/{{monLocalJob.id}}" text="{{ _("monitoring_monjobs_deleteconf") }}"
                                    class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" data-placement="top" title="{{ _("monitoring_monjobs_delete") }}"><i class="fa fa-trash-o"></i></a>
                            </td>
                            <td>
                                <span title="ID: {{ monLocalJob.getId() }}" data-toggle="tooltip">
                                    {{ monLocalJob.getShortname('physical') }}
                                </span>
                            </td>
                            <td>
                                {{ monLocalJob.getStatus() }}
                            </td>
                        </tr>
                    {% endfor %}
                {% endif %}
                </tbody>
            </table>
        </div>
        <div class="col-md-6 col-xs-12 removePadding">
            <table class="table table-striped table-condensed table-hover panel-sub-table">
                <tbody>
                    <tr>
                        <th colspan="4">Remote</th>
                    </tr>
                {% if item.getMonRemoteJobs().count() == 0 %}
                    <tr>
                        <td colspan="4">
                            {{ _("monitoring_monremotejobs_notfound") }}
                        </td>
                    </tr>
                {% else %}
                    {% for monRemoteJob in item.getMonRemoteJobs() %}
                        {% set statusClass = '' %}
                        {% if monRemoteJob.getStatus() == 'down' %}
                            {% set statusClass = 'danger' %}
                        {% endif %}
                        <tr class="{{ statusClass }}">
                            <td class="tableBtns">
                                {{ link_to("physical_servers/monJobsEdit/"~monRemoteJob.id,'<i class="fa fa-pencil"></i>',
                                    'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monjobs_edit")) }}
                                {{ link_to("physical_servers/monJobsDetails/"~monRemoteJob.id,'<i class="fa fa-list"></i>',
                                    'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("monitoring_monjobs_details")) }}
                                {% if monRemoteJob.getMuted() == 0 %}
                                    <a href="#/" link="/physical_servers/monJobsMute/{{monRemoteJob.id}}" text="{{ _("monitoring_monjobs_muteconf") }}"
                                        class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" title="{{ _("monitoring_monjobs_mute") }}"><i class="fa fa-volume-off"></i></a>
                                {% else %}
                                    <a href="#/" link="/physical_servers/monJobsMute/{{monRemoteJob.id}}" text="{{ _("monitoring_monjobs_unmuteconf") }}"
                                        class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" title="{{ _("monitoring_monjobs_unmute") }}"><i class="fa fa-volume-up"></i></a>
                                {% endif %}
                                <a href="#/" link="/physical_servers/monJobsDelete/{{monRemoteJob.id}}" text="{{ _("monitoring_monjobs_deleteconf") }}"
                                    class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" data-placement="top" title="{{ _("monitoring_monjobs_delete") }}"><i class="fa fa-trash-o"></i></a>
                            </td>
                            <td>
                                <span title="ID: {{ monRemoteJob.getId() }}" data-toggle="tooltip">
                                    {{ monRemoteJob.getShortname('physical') }}
                                </span>
                            </td>
                            <td>
                                {% if monRemoteJob.getUptime() != '' %}
                                {% set uptime = monRemoteJob.getUptime()|json_decode %}
                                    <span data-toggle="tooltip" data-html="true" title="
                                        {{ monRemoteJob.getStatus() }} seit {{ monRemoteJob.getLastStatusChange() }}<br /><br />
                                        {{ round(uptime.actperioduppercentage*100,3) }}% (dieser und vorheriger Monat)<br />
                                        {{ round(uptime.actyearuppercentage*100,3) }}% (dieses Jahr)<br />
                                        {{ round(uptime.everuppercentage*100,3) }}% (immer)<br />
                                    ">
                                        {{ round(uptime.actperioduppercentage * 100,3) }}%
                                    </span>
                                {% else %}
                                        {{ _("monitoring_monjobs_no_uptime") }}
                                {% endif %}
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
</div>