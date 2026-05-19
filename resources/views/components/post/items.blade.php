@props(['posts'])

@foreach($posts as $post)
<x-post :post="$post" />
@endforeach