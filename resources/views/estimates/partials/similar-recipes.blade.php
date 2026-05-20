@php
    /** @var list<array<string, mixed>> $similarRecipes */
    $similarRecipes = $similarRecipes ?? [];
@endphp

@if (count($similarRecipes) > 0)
    <div class="card border-grey mb-3">
        <div class="card-body p-3">
            <h6 class="f-15 font-weight-bold text-dark mb-2">@lang('modules.estimates.similarRecipesTitle')</h6>
            <p class="f-12 text-muted mb-2">@lang('modules.estimates.similarRecipesHint')</p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0 f-13">
                    <thead class="bg-light-grey">
                        <tr>
                            <th>@lang('modules.estimates.estimatesNumber')</th>
                            <th>@lang('app.client')</th>
                            <th>@lang('modules.estimates.recipeMoq')</th>
                            <th>@lang('modules.estimates.similarMatch')</th>
                            <th>@lang('modules.estimates.grossMarginPercent')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($similarRecipes as $match)
                            <tr>
                                <td>
                                    <a href="{{ $match['url'] }}" class="text-dark-grey openRightModal">{{ $match['estimate_number'] }}</a>
                                </td>
                                <td>{{ $match['client_name'] ?? '—' }}</td>
                                <td>{{ $match['recipe_moq'] ?? '—' }}</td>
                                <td>{{ $match['match_score'] }}%</td>
                                <td>
                                    @if ($match['gross_margin_percent'] !== null)
                                        {{ number_format((float) $match['gross_margin_percent'], 2) }}%
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
