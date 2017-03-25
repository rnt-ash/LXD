{# Change root password form #}

{{ partial("partials/core/partials/renderFormElement") }}      

<div class="page-header">
    <h2><i class="fa fa-server" aria-hidden="true"></i> {{ _("virtualserver_change_root_password") }}</h2>
</div>

<div class="well">
    <div class="row">
        {{ form("virtual_servers/rootPasswordChangeExecute", 'role': 'form') }}

        {{ form.get('virtual_servers_id').render() }}
        
        {% if form.hasMessagesFor('virtual_servers_id') %}
            <div class="alert alert-danger" role="alert">{{form.getMessagesFor('id')[0]}}</div>  
        {% endif %}

        {{ renderElement('password',form,12,'genPW') }}

        <div class="col-lg-12">
            {{ submit_button( _("virtualserver_save") , "class": "btn btn-primary loadingScreen") }}
            {{ link_to('/virtual_servers/slidedata', _("virtualserver_cancel"), 'class': 'btn btn-default pull-right') }}
        </div>
                
        </form>
    </div>
</div>
