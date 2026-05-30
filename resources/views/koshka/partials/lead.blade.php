@php
  $fields = collect($lead['field_data'] ?? []);
  $get = fn($names) => optional($fields->first(fn($f) => in_array($f['name'] ?? '', (array)$names)))['values'][0] ?? '';
  $name  = $get(['full_name','first_name']);
  $phone = $get(['phone_number','phone']);
  $phoneClean = preg_replace('/[^\d+]/', '', $phone);
  $waPhone = ltrim($phoneClean, '0');
  if (preg_match('/^[1-9]/', $waPhone) && !str_starts_with($waPhone, '972')) $waPhone = '972' . $waPhone;
  $duration = $get('practice_duration');
  $time = $lead['created_time'] ?? '';
  $extra = $fields->filter(fn($f) => !in_array($f['name'] ?? '', ['full_name','first_name','last_name','phone_number','phone','email']));
@endphp
<div class="lead">
  <div class="lead-head">
    <div class="lead-name">{{ $name ?: '(ללא שם)' }}</div>
    <div class="lead-time" title="{{ $time }}">{{ $time ? \Carbon\Carbon::parse($time)->diffForHumans() : '' }}</div>
  </div>
  @if($phone)
    <div class="lead-phone"><a href="tel:{{ $phoneClean }}">{{ $phone }}</a></div>
  @endif
  @foreach($extra as $f)
    <div class="lead-field"><span class="k">{{ $f['name'] }}:</span><span class="v">{{ implode(', ', $f['values'] ?? []) }}</span></div>
  @endforeach
  <div class="lead-meta">
    @if(!empty($lead['_form_name']))<span>טופס: {{ $lead['_form_name'] }}</span>@endif
    @if(!empty($lead['campaign_name']))<span>קמפיין: {{ $lead['campaign_name'] }}</span>@endif
    @if(!empty($lead['ad_name']))<span>מודעה: {{ $lead['ad_name'] }}</span>@endif
  </div>
  <div class="actions">
    @if($phoneClean)<a class="act call" href="tel:{{ $phoneClean }}">📞 התקשרי</a>@endif
    @if($waPhone)<a class="act wa" href="https://wa.me/{{ $waPhone }}" target="_blank">💬 וואטסאפ</a>@endif
  </div>
</div>
