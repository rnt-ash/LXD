{# MonJobs new form #}

{{ partial("partials/core/partials/renderFormElement") }}      

<div class="page-header">
    <h2><i class="fa fa-server" aria-hidden="true"></i> {{ _("monitoring_monjobs_add") }}</h2>
</div>

<div class="well">
    <div class="row">
        {{ form("mon_jobs/monJobsAddExecute", 'role': 'form') }}

        {{ form.get('server_id').render() }}
        
        {% if form.hasMessagesFor('server_id') %}
            <div class="col-xs-12">
                <div class="alert alert-danger" role="alert">{{form.getMessagesFor('server_id')[0]}}</div>
            </div>
        {% endif %}

        <div class="clearfix">
            {{ renderElement('mon_behavior',form,6,'bootstrap-select-live-search') }}
        </div>
        <div class="clearfix">
            {{ renderElement('mon_contacts_message',form,6,'bootstrap-select-live-search') }}
            {{ renderElement('mon_contacts_alarm',form,6,'bootstrap-select-live-search') }}
        </div>
        
        <div class="col-lg-12">
            {{ submit_button( _("monitoring_monjobs_save") , "class": "btn btn-primary loadingScreen") }}
            {{ link_to('/mon_jobs/cancel', _("monitoring_monjobs_cancel"), 'class': 'btn btn-default pull-right') }}
        </div>
                
        </form>
    </div>
</div>
