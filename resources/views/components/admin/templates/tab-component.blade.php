@props([
    'tabs' => [],
    'targets' => []
])

<style>
ul {
    margin: 0;
    padding: 0;
    list-style: none;
}
a {
    text-decoration: none;
    color: black;
}
.tab-menu-setting:hover {
    background-color: #eeeeee;
}
a.tab-menu-setting.active {
    background-color: #eeeeee;
}
</style>

<div class="col-md-2 mb-4 mb-lg-0">
    <ul class="bg-white rounded w-100 m-0 px-3 py-4">
        @foreach($tabs as $key => $value)    
            <li class="w-100 mb-3">
                <a href="#" data-tabs-target="#{{ $value['id'] }}" data-tabs="{{ $value['id'] }}" class="px-3 tab-menu-setting border py-2 d-block rounded">{{ $value['title'] }}</a>
            </li>
        @endforeach
    </ul>
</div>
<div class="col-md-10">
    @foreach($targets as $key => $value)
        {{ $$value }}
    @endforeach
</div>

<script>
    @php
        $tabs = array_map(function($item) {
            return $item['id'];
        }, $tabs);

        $targets = array_map(function($item) {
            return "#$item";
        }, $targets);

    @endphp
    
    $.customTab({
        tabs: JSON.parse('{!! json_encode($tabs) !!}'),
        targets: JSON.parse('{!! json_encode($targets) !!}')
    });
</script>