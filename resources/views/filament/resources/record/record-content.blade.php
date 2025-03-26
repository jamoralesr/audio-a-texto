@php
    $record = $getRecord();
    $content = $record ? $record->content : '';
@endphp

<div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
    <div class="prose dark:prose-invert max-w-none">
        {!! Str::markdown($content) !!}
    </div>
</div>
