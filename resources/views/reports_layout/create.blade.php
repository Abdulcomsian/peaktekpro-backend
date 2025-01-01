@extends('layouts.report-layout')

@section('title', 'Create Report')

@section('content')
    <section class="h-screen flex">
        <!-- Sidebar with Tabs -->
        {{-- <aside class="w-1/4 p-4 bg-gray-100 shadow overflow-y-auto h-full"> --}}
        <aside
            class="w-1/4 p-4 bg-white shadow overflow-y-auto h-full scrollbar-thin scrollbar-thumb-blue-600 scrollbar-track-blue-300">
            <ul id="tabsList" class="space-y-2">

                @forelse ($pages as $page)
                    <li class="tab-item bg-blue-200 p-2 rounded cursor-pointer" data-target="#tab{{ $page->id }}"
                        data-id="{{ $page->id }}">{{ $page->name }}</li>
                @empty
                @endforelse
            </ul>
        </aside>

        <!-- Main Content Area -->
        <section class="w-3/4 p-4">
            <div class="bg-white">

            </div>
            @forelse ($pages as $page)
                <div id="tab{{ $page->id }}" class="tab-content bg-white p-4 shadow rounded hidden">
                    {{ $page->name }}
                </div>
            @empty
            @endforelse
        </section>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Show the first tab content by default
            $(".tab-content").hide();
            $("#content1").show();

            // Tab click handler
            $(".tab-item").on("click", function() {
                $(".tab-item").removeClass("bg-blue-400").addClass("bg-blue-200");
                $(this).addClass("bg-blue-400");

                // Show the related content
                $(".tab-content").hide();
                $($(this).data("target")).fadeIn();
            });

            // Enable draggable tabs
            $("#tabsList").sortable({
                opacity: 0.5,
                start: function(event, ui) {
                    ui.item.css("background-color",
                    "rgba(96, 165, 250, 0.5)"); // Set opacity of dragging item
                },
                stop: function(event, ui) {
                    ui.item.css("background-color", ""); // Reset color on drag stop

                    // Update order via AJAX after drag stop
                    const order = $("#tabsList .tab-item").map(function() {
                        return $(this).data("id");
                    }).get();

                    $.ajax({
                        url: "{{ route('reports.page-ordering.update') }}", // Define this route in your web.php
                        method: 'POST',
                        data: {
                            order: order,
                        },
                        success: function(response) {
                            console.log("Order updated successfully!");
                        },
                        error: function() {
                            console.error("Order update failed.");
                        }
                    });
                }
            });
        });
    </script>
@endpush
