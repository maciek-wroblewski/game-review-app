<x-layout>
    <div class="container py-4">
        <div class="row justify-content-center posts-container">
            <div class="col-md-8">


                @auth
                <x-post.create-form />
                @endauth
                <div class="container">
                    <h1>Global Timeline</h1>

                    {{-- Call your unified component --}}
                    <x-post.list :posts="$posts" />

                </div>

            </div>
        </div>
    </div>
</x-layout>