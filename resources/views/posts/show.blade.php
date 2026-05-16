<x-layout>
    <div class="container py-4">
        <div class="row justify-content-center posts-container">
            <div class="col-md-8">

                <x-post :post="$post" />
                <p>Comments:</p>
                @foreach($replies as $cmt)
                <x-post.comment :post="$cmt" />
                @endforeach
                <div class="pagination-container mt-5">
                    <div class="pagination-wrapper shadow-sm">
                        {{ $replies->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-layout>