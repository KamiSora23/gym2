@php $bolsa = $errors->getBag($bag ?? 'default'); @endphp

@if ($bolsa->any())
    <div class="alert alert-error" style="margin:1rem 0 0;">
        <i class="fas fa-circle-exclamation"></i>
        <ul>
            @foreach ($bolsa->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
