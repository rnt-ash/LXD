{# MonJobs edit form #}

{{ partial("partials/core/partials/renderFormElement") }}      

<div class="page-header">
    <h2><i class="fa fa-server" aria-hidden="true"></i> {{ _("monitoring_monjobs_edit") }}</h2>
</div>

<div class="well">
    <h3>MonJob: {{ monJobName }} {{ _("monitoring_monjobs_on_server") }} {{ serverName }}</h3>
</div>

<div class="well">
    <div class="row">
        {{ form("physical_servers/monJobsEditExecute", 'role': 'form') }}

        {{ form.get('id').render() }}
        
        {% if form.hasMessagesFor('id') %}
            <div class="col-xs-12">
                <div class="alert alert-danger" role="alert">{{form.getMessagesFor('id')[0]}}</div>
            </div>
        {% endif %}

        {{ renderElement('mon_behavior_params',form) }}
        <div class="clearfix">
            {{ renderElement('period',form,3) }}
            {{ renderElement('alarm_period',form,3) }}
            {{ renderElement('warning_value',form,3) }}
            {{ renderElement('maximal_value',form,3) }}
        </div>
        <input type="hidden" name="active" value="0">
        {{ renderElement('active',form,12,'check') }}
        <input type="hidden" name="alarm" value="0">
        {{ renderElement('alarm',form,12,'check') }}
        <input type="hidden" name="healing" value="0">
        {{ renderElement('healing',form,12,'check') }}
        {{ renderElement('mon_contacts_message',form,6,'bootstrap-select-live-search') }}
        {{ renderElement('mon_contacts_alarm',form,6,'bootstrap-select-live-search') }}
        
        <div class="col-lg-12">
            {{ submit_button( _("physicalserver_save") , "class": "btn btn-primary loadingScreen") }}
            {{ link_to('/physical_servers/slidedata', _("physicalserver_cancel"), 'class': 'btn btn-default pull-right') }}
        </div>
                
        </form>
    </div>
</div>
