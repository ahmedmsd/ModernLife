@php
    $phases = $getState() ?? [];
@endphp

@if(empty($phases))
    <div style="padding: 16px; text-align: center; color: #6b7280;">لم تبدأ أي مرحلة بعد</div>
@else
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th scope="col" class="px-4 py-3">المرحلة</th>
                    <th scope="col" class="px-4 py-3">بدأت في</th>
                    <th scope="col" class="px-4 py-3">انتهت في</th>
                    <th scope="col" class="px-4 py-3">المدة</th>
                    <th scope="col" class="px-4 py-3 text-center">الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($phases as $index => $phase)
                    @php
                        $rowClass = 'border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 ' . 
                            ($index % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800');
                            
                        $startFormatted = \Carbon\Carbon::parse($phase['startTime'])->format('Y-m-d H:i');
                        $endFormatted = $phase['endTime'] 
                            ? \Carbon\Carbon::parse($phase['endTime'])->format('Y-m-d H:i') 
                            : '<span class="text-gray-400 dark:text-gray-600">—</span>';
                        
                        // Status badge
                        if ($phase['isCompleted']) {
                            if (!empty($phase['wasRejected'])) {
                                $badgeClass = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
                                $label = '✗ رُفضت';
                            } else {
                                $badgeClass = 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300';
                                $label = '✓ مكتملة';
                            }
                        } elseif (!empty($phase['isCurrent'])) {
                            $badgeClass = 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300';
                            $label = '● حالية';
                        } else {
                            $badgeClass = 'bg-yellow-200 text-yellow-800 dark:bg-yellow-900/60 dark:text-yellow-300';
                            $label = '⏳ جاري';
                        }
                    @endphp

                    <tr class="{{ $rowClass }}">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $phase['name'] }}</td>
                        <td class="px-4 py-3 font-mono text-xs" dir="ltr">{{ $startFormatted }}</td>
                        <td class="px-4 py-3 font-mono text-xs" dir="ltr">{!! $endFormatted !!}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-white font-semibold">{{ $phase['duration'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="{{ $badgeClass }} text-xs font-medium px-2.5 py-0.5 rounded border border-transparent">
                                {{ $label }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
