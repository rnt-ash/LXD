{# Edit customer form #}

{{ partial("partials/core/partials/renderFormElement") }}       

<div class="page-header">
    <h2><i class="fa fa-globe" aria-hidden="true"></i> Colocations</h2>
</div>

<div class="well">
    <div class="row">
        {{ form("colocations/save", 'role': 'form') }}
        {{ form.get('id').render() }}

        {% if form.hasMessagesFor('id') %}
            <div class="alert alert-danger" role="alert">{{form.getMessagesFor('id')[0]}}</div>  
        {% endif %}

        {{ renderElement('name',form,6) }}
        {{ renderElement('customers_id',form,6) }}
        {{ renderElement('description',form) }}
        {{ renderElement('location',form,6) }}
        {{ renderElement('activation_date',form,6) }}
        
        <div class="col-lg-12">
            {{ submit_button("Save", "class": "btn btn-primary") }}
            {{ link_to('/colocations/slidedata', 'Cancel', 'class': 'btn btn-default pull-right') }}
        </div>
                
        </form>
    </div>
</div>
