@props([
    'collection' => null,
    'forms' => null
])

<x-frontend.templates.hero-banner 
    :banners="$collection->where('an', 1)->latest()->get()"
    :forms="$forms"
/>