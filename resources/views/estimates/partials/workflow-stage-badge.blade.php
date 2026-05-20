@php
    /** @var \App\Models\Estimate $estimate */
    $stage = $estimate->workflowStagePresentation();
@endphp
<span class="badge {{ $stage['badge_class'] }} f-12">{{ $stage['label'] }}</span>
