<x-layout>
    <div class="container py-4">
        <div class="row justify-content-center posts-container">
            <div class="col-md-8">


                <x-post.create-form />
                <div class="container">
                    <h1>{{ __('posts.global_timeline') }}</h1>

                    {{-- Call your unified component --}}
                    <x-post.list :posts="$posts" />

                </div>

            </div>
        </div>
    </div>
</x-layout>