@foreach ($list['data'] as $item)
    <li class="flex items-center event-item" data-event-info="{{ json_encode($item) }}">
        <svg class="w-3.5 h-3.5 mr-2 @if($item['read']) text-green-500 dark:text-green-40 @else text-gray-500 dark:text-gray-400 @endif flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
        </svg>
        {{ $item['name'] }}
    </li>
@endforeach

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $(".event-item").on("click", function() {
            let eventItem = $(this).data("event-info");
            // let eventItem = JSON.parse(eventInfo);
            updateEventStatus(eventItem);
        });

        function updateEventStatus(eventItem) {
            $.ajax({
                url: "/api/user/updateReadStatus",
                method: "PUT",
                data: {
                    _token: "{{ csrf_token() }}",
                    source: eventItem.source,
                    entry_id: eventItem.id,
                    status: eventItem.read
                },
                success: function(response) {
                    console.log("Event status updated.");
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
        }
    });
</script>
