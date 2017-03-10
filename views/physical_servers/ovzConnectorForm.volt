{# Edit connector form #}

{{ partial("partials/core/partials/renderFormElement") }}      

<div class="page-header">
    <h2><i class="fa fa-server" aria-hidden="true"></i> {{ _("physicalserver_connect_title") }}</h2>
</div>

<div class="well">
    <h2>{{ _("physicalserver_connection_prepare_title") }}</h2>
    {{ _("physicalserver_connection_prepare_instructions") }}
    <br />
        
    <code>
        <p>yum -y update<br />
        yum -y install mc ntp wget mailx nano php-cli php-pdo<br />
        ssh-keygen -b 2048 -t rsa -f /root/.ssh/id_rsa -q -N "" </p>
    </code>
</div>

<div class="well">
    <div class="row">
        {{ form("physical_servers/ovzConnectorExecute", 'role': 'form') }}

        {{ form.get('physical_servers_id').render() }}
        
        {% if form.hasMessagesFor('physical_servers_id') %}
            <div class="alert alert-danger" role="alert">{{form.getMessagesFor('id')[0]}}</div>  
        {% endif %}

        {{ renderElement('username',form) }}
        {{ renderElement('password',form) }}

        <div class="col-lg-12">
            {{ submit_button( _("physicalserver_connect_connectbutton") , "class": "btn btn-primary loadingScreen") }}
            {{ link_to('/physical_servers/slidedata', _("physicalserver_cancel"), 'class': 'btn btn-default pull-right') }}
        </div>
                
        </form>
    </div>
</div>
