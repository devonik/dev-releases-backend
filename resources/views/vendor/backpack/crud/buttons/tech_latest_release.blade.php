@if ($crud->hasAccess('update'))
    <a href="{{ url($crud->route.'/'.$entry->getKey().'/getLatestRelease') }} " class="btn btn-xs btn-default"><i class="fa fa-wifi"></i> Get latest release</a>
@endif
