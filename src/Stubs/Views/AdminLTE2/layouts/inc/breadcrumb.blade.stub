<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{$page_title}}
    </h1>
    <ol class="breadcrumb">
        @foreach(request()->segments() as $key => $value)
            @if($key == count(request()->segments()) -1)
                <li class="active">{{$value}}</li>
            @else
                <li class="active">{{$value}}</li>
            @endif
        @endforeach
    </ol>
</section>