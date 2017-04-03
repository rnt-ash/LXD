{# MonLocal Jobs form #}

{{ partial("partials/core/partials/renderFormElement") }}      

<div class="page-header">
    <h2><i class="fa fa-server" aria-hidden="true"></i> MonLocal Job</h2>
</div>

<div class="well">
    <div class="row">
        {{ form("virtual_servers/monLocalJobAddExecute", 'role': 'form') }}

        {{ form.get('server_id').render() }}
        
        {% if form.hasMessagesFor('server_id') %}
            <div class="alert alert-danger" role="alert">{{form.getMessagesFor('server_id')[0]}}</div>  
        {% endif %}

        {{ renderElement('mon_behavior_class',form) }}
        {{ renderElement('period',form) }}
        {{ renderElement('alarm_period',form) }}
        {{ renderElement('mon_contacts_message',form) }}
        {{ renderElement('mon_contacts_alarm',form) }}
        
        <div class="col-lg-12">
            {{ submit_button( _("virtualserver_save") , "class": "btn btn-primary loadingScreen") }}
            {{ link_to('/virtual_servers/slidedata', _("virtualserver_cancel"), 'class': 'btn btn-default pull-right') }}
        </div>
                
        </form>
    </div>
</div>
