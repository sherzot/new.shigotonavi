@php
    // Step holatlarini aniqlash
    $stepStatus = [
        'register' => 'upcoming',  // 会員登録
        'resume'   => 'upcoming',  // 履歴書
        'search'   => 'upcoming',  // 求人検索
    ];

    if (!$user || !$hasBasicInfo) {
        // Foydalanuvchi hali ro'yxatdan o'tmagan
        $stepStatus['register'] = 'current';
    } elseif ($user && $hasBasicInfo && !$hasResume) {
        // Foydalanuvchi ro'yxatdan o'tgan, lekin resume kiritmagan
        $stepStatus['register'] = 'done';
        $stepStatus['resume']   = 'current';
    } else {
        // Hamma narsa to‘liq
        $stepStatus['register'] = 'done';
        $stepStatus['resume']   = 'done';
        $stepStatus['search']   = 'current';
    }

    $colorClass = [
        'done'     => 'bg-success text-white',
        'current'  => 'bg-primary text-white',
        'upcoming' => 'bg-secondary text-white',
    ];
@endphp

<div class="d-flex justify-content-center align-items-center flex-wrap my-4">
    @foreach (['register' => '会員登録', 'resume' => '履歴書', 'search' => '求人検索'] as $key => $label)
        <div class="d-flex align-items-center">
            <span class="badge rounded-pill px-4 py-2 {{ $colorClass[$stepStatus[$key]] }}">
                {{ $label }}
            </span>
            @if (! $loop->last)
                <i class="fa-solid fa-chevron-right fs-4 mx-2
                   {{ $stepStatus[$key] === 'done'    ? 'text-success'
                    : ($stepStatus[$key] === 'current' ? 'text-primary'
                    : 'text-secondary') }}"></i>
            @endif
        </div>
    @endforeach
</div>
