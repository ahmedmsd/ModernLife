@php
    $value = (isset($getState) && is_callable($getState)) ? $getState() : ($state ?? null);

    $comments = $value instanceof \Illuminate\Support\Collection ? $value : collect($value);
@endphp

@if ($comments->isEmpty())
    <div class="text-gray-500">لا توجد تعليقات بعد.</div>
@else
    <ul class="space-y-4">
        @foreach ($comments as $c)
            @php
                $authorName = $c->author->name ?? data_get($c, 'author.name', '—');
                $createdAt  = $c->created_at ?? data_get($c, 'created_at');
                $createdForHumans = $createdAt
                    ? (\Illuminate\Support\Carbon::parse($createdAt))->diffForHumans()
                    : '';
                $body = $c->body ?? data_get($c, 'body', '');
                $attachments = $c->attachments ?? data_get($c, 'attachments', []);
                $attachments = is_array($attachments) ? $attachments : [];
            @endphp

            <li class="rounded-xl border p-4">
                <div class="text-sm text-gray-600 flex flex-wrap items-center gap-2">
                    <span class="font-medium">{{ $authorName }}</span>
                    <span>•</span>
                    <span>{{ $createdForHumans }}</span>
                </div>

                <div class="mt-2 whitespace-pre-wrap break-words">
                    {{ $body }}
                </div>

                @if (count($attachments))
                    <div class="mt-2 text-sm flex flex-wrap gap-2">
                        @foreach ($attachments as $path)
                            @php
                                $url  = \Storage::disk('public')->url($path);
                                $name = basename($path);
                            @endphp
                            <a class="underline" href="{{ $url }}" target="_blank" rel="noopener">
                                {{ $name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
@endif
