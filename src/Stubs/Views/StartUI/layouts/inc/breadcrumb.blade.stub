<header class="section-header">
    <div class="tbl">
        <div class="tbl-row">
            <div class="tbl-cell">
                <h3>{{$title}}</h3>
                <ol class="breadcrumb breadcrumb-simple">
                    @foreach(request()->segments() as $key => $value)
                        @if($key == count(request()->segments()) -1)
                            <li class="active">{{$value}}</li>
                        @else
                            <li><a href="#">{{$value}}</a></li>
                        @endif
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</header>