@php
    $current = $model->getPath();
    $preview = \App\Services\UrlHistoryService::previewPath($model, old('slug'), old('parent_id'));
@endphp
<div class="rounded-md border border-gray-200 bg-gray-50 p-4 space-y-1 text-sm">
    <p class="text-gray-600">
        Current URL:
        <code class="ml-1 break-all text-gray-900">{{ $current }}</code>
    </p>

    @if ($preview && $preview !== $current)
        <p class="text-gray-600">
            Will become:
            <code class="ml-1 break-all text-gray-900">{{ $preview }}</code>
        </p>
        <p class="text-amber-700">
            Saving will change the public URL. Existing links will keep working through automatic redirects.
        </p>

        @if ($model instanceof \App\Models\Service || $model instanceof \App\Models\Destination)
            @php
                $affectedCount = count($model->descendantIds());
            @endphp
            @if ($affectedCount > 0)
                <p class="text-amber-700">
                    This also changes {{ $affectedCount }} child {{ $affectedCount === 1 ? 'URL' : 'URLs' }} beneath this item, which will redirect automatically too.
                </p>
            @endif
        @endif
    @endif
</div>