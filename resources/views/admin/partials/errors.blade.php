@if($errors->any())
    <x-admin.ui.alert tone="danger" title="Please fix the following">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-admin.ui.alert>
@endif
