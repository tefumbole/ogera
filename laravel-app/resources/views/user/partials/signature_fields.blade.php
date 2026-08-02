{{--
    Sign / Comment / Approve images for a user.

    Each one can be drawn on the pad or uploaded, and whichever arrives is
    stored as a cropped transparent PNG. Expects an optional $lims_user_data.
--}}
@php
    $signatureUser = $lims_user_data ?? null;
    $signatureFields = [
        ['key' => 'sign', 'label' => trans('file.Sign'), 'empty' => 'No sign found'],
        ['key' => 'stemp', 'label' => trans('file.Stemp'), 'empty' => 'No Comment found'],
        ['key' => 'approve', 'label' => trans('file.Approve'), 'empty' => 'No Approve found'],
    ];
@endphp

<style>
    .sig-field .sig-field-current { margin-bottom: 8px; min-height: 28px; }
    .sig-field .sig-field-current img { max-height: 60px; max-width: 100%; background: #fff; }
    .sig-field .sig-field-empty { color: #8a94a6; font-size: 13px; }
    .sig-field .sig-field-buttons { margin-bottom: 8px; }
    .sig-field .sig-field-hint { display: block; color: #8a94a6; font-size: 12px; margin-top: 4px; }
</style>

@foreach($signatureFields as $field)
    @php
        $key = $field['key'];
        $current = $signatureUser ? $signatureUser->{$key} : null;
        $currentUrl = \App\Support\UserSignature::url($current);
    @endphp
    <div class="form-group sig-field" id="{{ $key }}">
        <label><strong>{{ $field['label'] }}</strong></label>

        <div class="sig-field-current">
            <img data-signature-preview="{{ $key }}_data" alt="{{ $field['label'] }}">
            @if($currentUrl)
                <img src="{{ $currentUrl }}" data-signature-state="{{ $key }}_data" alt="{{ $field['label'] }}">
            @else
                <span class="sig-field-empty" data-signature-state="{{ $key }}_data">{{ $field['empty'] }}</span>
            @endif
        </div>

        <input type="hidden" name="{{ $key }}_data" id="{{ $key }}_data">

        <div class="sig-field-buttons">
            <button type="button" class="btn btn-sm btn-primary" data-signature-pad="{{ $key }}_data">
                <i class="dripicons-pencil"></i> {{ $currentUrl ? 'Draw a new one' : 'Draw ' . strtolower($field['label']) }}
            </button>
            <button type="button" class="btn btn-sm btn-link" data-signature-clear="{{ $key }}_data">Discard drawing</button>
        </div>

        <input type="file" class="form-control" name="{{ $key }}" accept="image/*">
        <small class="sig-field-hint">Draw it, upload a file, or paste an image. It is saved as a transparent PNG.</small>
    </div>
@endforeach
