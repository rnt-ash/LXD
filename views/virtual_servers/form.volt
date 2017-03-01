{# Edit virtial server form #}

{{ partial("partials/core/partials/renderFormElement") }}

<div class="page-header">
    <h2><i class="fa fa-cube" aria-hidden="true"></i> Virtual Servers</h2>
</div>

<div class="well">
    <div class="row">
        {{ form("virtual_servers/save", 'role': 'form') }}
        {{ form.get('id').render() }}

        {% if form.hasMessagesFor('id') %}
            <div class="alert alert-danger" role="alert">{{form.getMessagesFor('id')[0]}}</div>  
        {% endif %}

        {{ renderElement('name',form,6) }}
        {{ renderElement('customers_id',form,6) }}
        {{ renderElement('physical_servers_id',form,6) }}
        {{ renderElement('ostemplate',form,6) }}
        {{ renderElement('password',form,6) }}
        {{ renderElement('distribution',form,6) }}
        {{ renderElement('core',form,6) }}
        {{ renderElement('memory',form,6) }}
        {{ renderElement('space',form,6) }}
        {{ renderElement('activation_date',form,6) }}
        {{ renderElement('description',form) }}

        <div class="col-lg-12">
            {{ submit_button("Save", "class": "btn btn-primary") }}
            {{ link_to('/virtual_servers/slidedata', 'Cancel', 'class': 'btn btn-default pull-right') }}
        </div>
        </form>
    </div>
</div>
