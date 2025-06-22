@php
    $themes = app_themes();
@endphp
<style>
    :root {
        --main-background: {{ $themes['main_background']['type'] == 'solid' ? $themes['main_background']['solid'] : $themes['main_background']['gradient']['colors'][0]['color'] }};
        --size-h1: {{ $themes['typography']['size']['h1'] ?? '1.5em' }};
        --size-h2: {{ $themes['typography']['size']['h2'] ?? '1.3em' }};
        --size-h3: {{ $themes['typography']['size']['h3'] ?? '1.1em' }};
        --size-h4: {{ $themes['typography']['size']['h4'] ?? '1em' }};
        --size-h5: {{ $themes['typography']['size']['h5'] ?? '0.9em' }};
        --size-h6: {{ $themes['typography']['size']['h6'] ?? '0.8em' }};
        --size-3xl: {{ $themes['typography']['size']['3xl'] ?? '2.3em' }};
        --size-2xl: {{ $themes['typography']['size']['2xl'] ?? '2em' }};
        --size-xl: {{ $themes['typography']['size']['xl'] ?? '1.5em' }};
        --size-lg: {{ $themes['typography']['size']['lg'] ?? '1.2em' }};
        --size-md: {{ $themes['typography']['size']['md'] ?? '1em' }};
        --size-sm: {{ $themes['typography']['size']['sm'] ?? '0.9em' }};
        --size-xs: {{ $themes['typography']['size']['xs'] ?? '0.8em' }};
        --primary-color: {{ $themes['typography']['colors']['primary'] }};
        --secondary-color: {{ $themes['typography']['colors']['secondary'] }};
        --light-color: {{ $themes['typography']['colors']['light'] }};
        --light-blue-color: {{ $themes['typography']['colors']['light_blue'] }};
        --dark-color: {{ $themes['typography']['colors']['dark'] }};
    }

    .dinamic_text_size-h1 {
        font-size: var(--size-h1);
        font-weight: bold;
    }

    .dinamic_text_size-h2 {
        font-size: var(--size-h2);
        font-weight: bold;
    }

    .dinamic_text_size-h3 {
        font-size: var(--size-h3);
        font-weight: bold;
    }

    .dinamic_text_size-h4 {
        font-size: var(--size-h4);
        font-weight: bold;
    }

    .dinamic_text_size-h5 {
        font-size: var(--size-h5);
        font-weight: bold;
    }

    .dinamic_text_size-h6 {
        font-size: var(--size-h6);
        font-weight: bold;
    }

    .dinamic_text_size-2xl {
        font-size: var(--size-2xl);
    }

    .dinamic_text_size-xl {
        font-size: var(--size-xl);
    }

    .dinamic_text_size-lg {
        font-size: var(--size-lg);
    }

    .dinamic_text_size-md {
        font-size: var(--size-md);
    }

    .dinamic_text_size-sm {
        font-size: var(--size-sm);
    }

    .dinamic_text_size-xs {
        font-size: var(--size-xs);
    }

    .dinamic_text_color-primary {
        color: var(--primary-color);
    }

    .dinamic_text_color-secondary {
        color: var(--secondary);
    }

    .dinamic_text_color-dark {
        color: var(--dark);
    }

    .dinamic_background_color-primary {
        background-color: var(--primary-color);
    }

    .dinamic_main_background {
        background-color: var(--main-background);
        
        @if($themes['main_background']['type'] == 'gradient')
            background: {{ generateGradientColor($themes['main_background']['gradient']) }};
        @endif
    }
</style>