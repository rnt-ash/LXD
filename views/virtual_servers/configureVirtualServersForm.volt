{# Edit virtual server form #}

{{ partial("partials/core/partials/renderFormElement") }}   

<div class="page-header">
    <h2><i class="fa fa-cube" aria-hidden="true"></i> Virtual Servers</h2>
</div>

<div class="well">
    <div class="row">
        {{ form("virtual_servers/sendConfigureVirtualServers", 'role': 'form') }}
        {{ form.get('virtual_servers_id').render() }}

        {% if form.hasMessagesFor('id') %}
            <div class="alert alert-danger" role="alert">{{form.getMessagesFor('id')[0]}}</div>  
        {% endif %}

        <div class="clearfix">
            {{ renderElement('hostname',form,6) }}
            {{ renderElement('dns',form,6) }}
        </div>
        <div class="clearfix">
            {{ renderElement('cores',form,6) }}
            {{ renderElement('memory',form,6) }}
        </div>
        <div class="clearfix">
            {{ renderElement('diskspace',form,6) }}
        </div>
        <input type="hidden" name="startOnBoot" value="0">
        {{ renderElement('startOnBoot',form,12,'check') }}
        
        {{ renderElement('description',form )}}

        <div class="col-lg-12">
            {{ submit_button("Save", "class": "btn btn-primary") }}
            {{ link_to('/virtual_servers/slidedata', 'Cancel', 'class': 'btn btn-default pull-right') }}
        </div>
        </form>
    </div>
</div>