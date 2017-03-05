{# Activate Repliva form #}

{{ partial("partials/core/partials/renderFormElement") }}   

<div class="page-header">
    <h2><i class="fa fa-cube" aria-hidden="true"></i>{{ _("virtualserver_replica") }}</h2>
</div>

<div class="well">
    {{ form("virtual_servers/ovzReplicaActivateExecute", 'role': 'form') }}

    {{ form.get('virtual_servers_id').render() }}
    {% if form.hasMessagesFor('virtual_servers_id') %}
        <div class="alert alert-danger" role="alert">{{form.getMessagesFor('id')[0]}}</div>  
    {% endif %}

    {{ renderElement('physical_server',form,12) }}

    {{ submit_button(_("virtualserver_replica_"), "class": "btn btn-primary") }}
    {{ link_to('/virtual_servers/slidedata', _("virtualserver_cancel"), 'class': 'btn btn-default pull-right') }}
            
    </form>
</div>