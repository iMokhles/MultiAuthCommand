<script src="{{ asset('start_ui') }}/js/lib/pnotify/pnotify.js"></script>
<script src="{{ asset('start_ui') }}/js/lib/pnotify/pnotify-init.js"></script>
{{-- Bootstrap Notifications using Prologue Alerts --}}
<script type="text/javascript">
    jQuery(document).ready(function($) {

        PNotify.prototype.options.styling = "bootstrap3";
        PNotify.prototype.options.styling = "fontawesome";

        @foreach (Alert::getMessages() as $type => $messages)
        @foreach ($messages as $message)

        $(function(){
            new PNotify({
                // title: 'Regular Notice',
                text: "{!! str_replace('"', "'", $message) !!}",
                type: "{{ $type }}",
                icon: false
            });
        });

        @endforeach
        @endforeach
    });
</script>