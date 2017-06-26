{% set uptime = monJob.getUptime()|json_decode %}

<div class="page-header">
    <h2>{{ monJob.getShortName('virtual') }} Details <span class="small">({{ serverName }})</span></h2>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="well">
            <table class="table table-condensed">
                <thead>
                </thead>
                <tbody>
                    <tr>
                        <th width="30%">{{ _("monitoring_monjobs_details_id") }}</th>
                        <td>{{ monJob.getId() }}</td>
                    </tr>
                    <tr>
                        <th>{{ _("monitoring_monjobs_details_last_run") }}</th>
                        <td>{{ monJob.getLastRun() }}</td>
                    </tr>
                    <tr>
                        <th>{{ _("monitoring_monjobs_details_status") }}</th>
                        <td>{{ monJob.getStatus() }} {{ _("monitoring_monjobs_details_since") }} {{ monJob.getLastStatusChange() }}</td>
                    </tr>
                    <tr>
                        <th>{{ _("monitoring_monjobs_details_actperiodup") }}</th>
                        <td>{{ uptime.actperioduppercentage*100 }}%</td>
                    </tr>
                    <tr>
                        <th>{{ _("monitoring_monjobs_details_actyearup") }}</th>
                        <td>{{ uptime.actyearuppercentage*100 }}%</td>
                    </tr>
                    <tr>
                        <th>{{ _("monitoring_monjobs_details_everup") }}</th>
                        <td>{{ uptime.everuppercentage*100 }}%</td>
                    </tr>
                    <tr>
                        <th>{{ _("monitoring_monjobs_details_active") }}</th>
                        <td>{{ monJob.getActive() }}</td>
                    </tr>
                    <tr>
                        <th>{{ _("monitoring_monjobs_details_healing") }}</th>
                        <td>{{ monJob.getHealing() }}</td>
                    </tr>
                    <tr>
                        <th>{{ _("monitoring_monjobs_details_alarm") }}</th>
                        <td>{{ monJob.getAlarm() }}</td>
                    </tr>
                    <tr>
                        <th>{{ _("monitoring_monjobs_details_alarmed") }}</th>
                        <td>{{ monJob.getAlarmed() }}</td>
                    </tr>
                    <tr>
                        <th>{{ _("monitoring_monjobs_details_last_alarm") }}</th>
                        <td>{{ monJob.getLastAlarm() }}</td>
                    </tr>
                    <tr>
                        <th>{{ _("monitoring_monjobs_details_contacts_messsage") }}</th>
                        <td>{{ monJob.getActive() }}</td>
                    </tr>
                    <tr>
                        <th>{{ _("monitoring_monjobs_details_alarm_messsage") }}</th>
                        <td>{{ monJob.getActive() }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    {% if downtimes is not empty %}
    <div class="col-xs-12">
        <h2>Downtimes</h2>
        {% for downtime in downtimes  %}
            <div class="well">
                <div>{{ _("monitoring_monjobs_downtimes_from") }}: {{ strftime('%d.%m.%Y %H:%M:%S',strtotime(downtime.getStartString())) }}</div>
                <div>{{ _("monitoring_monjobs_downtimes_to") }}: {{ strftime('%d.%m.%Y %H:%M:%S',strtotime(downtime.getEndString())) }}</div>
                <div>{{ _("monitoring_monjobs_downtimes_duration") }}: {{ downtime.getDurationInSeconds() }} {{ _("monitoring_monjobs_downtimes_seconds") }}</div>
                <br />
                {% if downtime.getHealJob() is not empty %}
                    <h4>{{ _("monitoring_monjobs_downtimes_healjob") }}</h4>
                    <table class="table table-condensed">
                        {% set healJob = downtime.getHealJob() %}
                        <tr>
                            <th>{{ _("monitoring_monjobs_downtimes_id") }}</th>
                            <td>{{ healJob.getId() }}</td>
                        </tr>
                        <tr>
                            <th>{{ _("monitoring_monjobs_downtimes_done") }}</th>
                            <td>{{ healJob.getDone() }}</td>
                        </tr>
                        <tr>
                            <th>{{ _("monitoring_monjobs_downtimes_type") }}</th>
                            <td>{{ healJob.getType() }}</td>
                        </tr>
                        <tr>
                            <th>{{ _("monitoring_monjobs_downtimes_created") }}</th>
                            <td>{{ healJob.getCreated() }}</td>
                        </tr>
                        <tr>
                            <th>{{ _("monitoring_monjobs_downtimes_params") }}</th>
                            <td>{{ healJob.getParams() }}</td>
                        </tr>
                        <tr>
                            <th>{{ _("monitoring_monjobs_downtimes_sent") }}</th>
                            <td>{{ healJob.getSent() }}</td>
                        </tr>
                        <tr>
                            <th>{{ _("monitoring_monjobs_downtimes_executed") }}</th>
                            <td>{{ healJob.getExecuted() }}</td>
                        </tr>
                        {% if healJob.getError() is not empty %}
                        <tr>
                            <th>{{ _("monitoring_monjobs_downtimes_error") }}</th>
                            <td>{{ healJob.getError() }}</td>
                        </tr>
                        {% endif %}
                        {% if healJob.getWarning() is not empty %}
                        <tr>
                            <th>{{ _("monitoring_monjobs_downtimes_warning") }}</th>
                            <td>{{ healJob.getWarning() }}</td>
                        </tr>
                        {% endif %}
                        {% if healJob.getRetval() is not empty %}
                        <tr>
                            <th>{{ _("monitoring_monjobs_downtimes_retval") }}</th>
                            <td>{{ healJob.getRetval() }}</td>
                        </tr>
                        {% endif %}
                    </table>
                {% else %}
                    {{ _("monitoring_monjobs_downtimes_no_healjob") }}
                {% endif %}
            </div>
        {% endfor %}
    </div>
    {% endif %}
</div>