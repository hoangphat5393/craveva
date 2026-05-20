@php
    /** @var list<array{key: string, label: string, done: bool, current: bool}> $batchWorkflowSteps */
@endphp

<div class="bg-white rounded p-4 mt-3 mb-4 border">
    <h5 class="f-14 text-dark-grey font-weight-bold mb-2">@lang('production::app.batchCompletionWorkflowTitle')</h5>
    <p class="f-13 text-muted mb-3">@lang('production::app.batchCompletionWorkflowHelp')</p>
    <ol class="list-unstyled mb-0 pl-0">
        @foreach ($batchWorkflowSteps as $step)
            <li class="d-flex align-items-start mb-3 @if ($step['current']) font-weight-bold @endif">
                <span class="mr-3 mt-1">
                    @if ($step['done'])
                        <i class="fa fa-check-circle text-success f-16"></i>
                    @elseif ($step['current'])
                        <i class="fa fa-dot-circle text-primary f-16"></i>
                    @else
                        <i class="fa fa-circle text-light-grey f-16"></i>
                    @endif
                </span>
                <span class="f-14 @if ($step['done']) text-muted @endif">{{ $step['label'] }}</span>
            </li>
        @endforeach
    </ol>
</div>
