<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Account Restricted</title>

    {{-- If your app already loads Tailwind globally, this will work fine.
         If not, the page still looks okay because we also use basic CSS vars. --}}

    <style>
        :root{
            --an-bg: #0b0f17;
            --an-card: #0f1623;
            --an-card-2: #121b2b;
            --an-border: rgba(255,255,255,.08);
            --an-text: rgba(255,255,255,.92);
            --an-text-muted: rgba(255,255,255,.65);
            --an-shadow: rgba(0,0,0,.55);
            --an-danger: #ff4d4d;
            --an-warning: #f6c343;
            --an-success: #23c08b;
            --an-primary: #6d5efc;
            --an-btn: rgba(255,255,255,.08);
            --an-btn-text: rgba(255,255,255,.92);
            --an-link: #9aa7ff;
            --an-input-bg: rgba(255,255,255,.06);
            --an-input-border: rgba(255,255,255,.10);
            --an-input-text: rgba(255,255,255,.92);
        }
        body{
            margin:0;
            background: radial-gradient(1000px 500px at 20% 0%, rgba(109,94,252,.18), transparent 60%),
                        radial-gradient(900px 450px at 80% 10%, rgba(255,77,77,.12), transparent 65%),
                        var(--an-bg);
            color: var(--an-text);
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji";
        }
        a{ color: var(--an-link); text-decoration:none; }
        a:hover{ text-decoration:underline; }
        .card{
            background: var(--an-card);
            border: 1px solid var(--an-border);
            box-shadow: 0 18px 45px var(--an-shadow);
        }
        .pill{
            display:inline-flex;
            align-items:center;
            gap:.5rem;
            border: 1px solid var(--an-border);
            background: var(--an-card-2);
            padding: .4rem .65rem;
            border-radius: 999px;
            font-size: 12px;
            color: var(--an-text-muted);
        }
        .btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:.5rem;
            height: 44px;
            padding: 0 16px;
            border-radius: 12px;
            border: 1px solid var(--an-border);
            background: var(--an-btn);
            color: var(--an-btn-text);
            cursor:pointer;
            text-decoration:none;
            font-weight:600;
        }
        .btn:hover{ filter: brightness(1.05); }
        .btn-danger{
            border-color: color-mix(in srgb, var(--an-danger) 30%, var(--an-border));
            background: color-mix(in srgb, var(--an-danger) 18%, transparent);
            color: var(--an-danger);
        }
        .btn-primary{
            background: var(--an-primary);
            border-color: color-mix(in srgb, var(--an-primary) 40%, var(--an-border));
            color: #fff;
        }
        .muted{ color: var(--an-text-muted); }
        .grid{
            display:grid;
            gap: 16px;
        }
    </style>
</head>

<body>
@php
    $user = auth()->user();
    $status = $user?->status ?? 'active';

    $isBanned = $status === 'banned';
    $isSuspended = $status === 'suspended';

    $until = $user?->suspended_until ? \Carbon\Carbon::parse($user->suspended_until) : null;

    $remaining = null;
    if ($isSuspended && $until && $until->isFuture()) {
        $remaining = $until->diffForHumans(now(), ['parts' => 3, 'short' => true]);
    }
@endphp

<div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;">
    <div class="card" style="width:100%; max-width:720px; border-radius:24px; overflow:hidden;">
        <div style="padding:20px 22px; border-bottom:1px solid var(--an-border); background:var(--an-card-2);">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                <div>
                    <div style="font-size:18px; font-weight:800; letter-spacing:.2px;">
                        Account Restricted
                    </div>
                    <div class="muted" style="font-size:13px; margin-top:4px;">
                        You can’t access AuraNexus while your account is restricted.
                    </div>
                </div>

                <span class="pill">
                    @if($isBanned)
                        <span style="width:8px;height:8px;border-radius:999px;background:var(--an-danger);"></span>
                        BANNED
                    @elseif($isSuspended)
                        <span style="width:8px;height:8px;border-radius:999px;background:var(--an-warning);"></span>
                        SUSPENDED
                    @else
                        <span style="width:8px;height:8px;border-radius:999px;background:var(--an-success);"></span>
                        RESTRICTED
                    @endif
                </span>
            </div>
        </div>

        <div style="padding:22px;">
            <div class="grid">
                <div style="border:1px solid var(--an-border); background:var(--an-card-2); border-radius:18px; padding:16px;">
                    <div style="font-weight:700; font-size:15px;">
                        @if($isBanned)
                            Your account has been permanently banned.
                        @elseif($isSuspended)
                            Your account is temporarily suspended.
                        @else
                            Your account is currently restricted.
                        @endif
                    </div>

                    <div class="muted" style="margin-top:8px; font-size:13px; line-height:1.6;">
                        @if($isBanned)
                            This restriction is permanent. If you believe this is a mistake, contact the site administration.
                        @elseif($isSuspended)
                            You’ll regain access when the suspension ends.
                        @else
                            Please contact the site administration for more details.
                        @endif
                    </div>

                    @if($isSuspended)
                        <div style="margin-top:14px; display:flex; flex-wrap:wrap; gap:10px;">
                            <span class="pill">
                                Suspended until:
                                <b style="color:var(--an-text); font-weight:800;">
                                    {{ $until ? $until->format('Y-m-d H:i') : '—' }}
                                </b>
                            </span>

                            <span class="pill">
                                Remaining:
                                <b style="color:var(--an-text); font-weight:800;">
                                    {{ $remaining ?? '—' }}
                                </b>
                            </span>
                        </div>
                    @endif
                </div>

                @if(!empty($user?->restricted_reason))
                    <div style="border:1px solid var(--an-border); background:var(--an-card); border-radius:18px; padding:16px;">
                        <div class="muted" style="font-size:12px; letter-spacing:.3px; text-transform:uppercase;">
                            Reason
                        </div>
                        <div style="margin-top:8px; white-space:pre-line; line-height:1.7;">
                            {{ $user->restricted_reason }}
                        </div>
                    </div>
                @endif

                <div style="display:flex; flex-wrap:wrap; gap:10px; justify-content:flex-end;">
                    {{-- Only allow logout --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-danger" type="submit">
                            Logout
                        </button>
                    </form>
                </div>

                <div class="muted" style="font-size:12px;">
                    Logged in as <b style="color:var(--an-text)">{{ $user?->username ?? '—' }}</b>.
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
