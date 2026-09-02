<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }
        .container { max-width: 960px; margin: 0 auto; padding: 3rem 1.5rem; }
        h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .subtitle { color: #64748b; margin-bottom: 2.5rem; }
        .section-title { font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.5rem; text-align: center; }
        .card img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 0.75rem; background: #e2e8f0; }
        .card .name { font-weight: 600; font-size: 1rem; }
        .card .position { color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; }
        .empty { color: #94a3b8; font-style: italic; }
        .links { margin-top: 2.5rem; display: flex; gap: 1rem; flex-wrap: wrap; }
        .links a { display: inline-block; padding: 0.5rem 1rem; background: #1e293b; color: #fff; border-radius: 0.5rem; text-decoration: none; font-size: 0.875rem; font-weight: 500; }
        .links a:hover { background: #334155; }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ config('app.name', 'Laravel') }}</h1>
        <p class="subtitle">Powered by <strong>Alumkit</strong></p>

        <h2 class="section-title">Executive Committee</h2>

        @php
            $members = \Alumkit\Alumkit\Facades\Alumkit::recentCommitteeMembers();
        @endphp

        @if ($members->isEmpty())
            <p class="empty">No committee members yet.</p>
        @else
            <div class="grid">
                @foreach ($members as $member)
                    <div class="card">
                        @if ($member->photoUrl())
                            <img src="{{ $member->photoUrl() }}" alt="{{ $member->displayName() }}">
                        @else
                            <img src="data:image/svg+xml,{{ rawurlencode('<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;80&quot; height=&quot;80&quot;><rect width=&quot;80&quot; height=&quot;80&quot; fill=&quot;%23e2e8f0&quot;/><text x=&quot;50%&quot; y=&quot;55%&quot; dominant-baseline=&quot;middle&quot; text-anchor=&quot;middle&quot; fill=&quot;%2394a3b8&quot; font-family=&quot;sans-serif&quot; font-size=&quot;28&quot;>' . substr($member->displayName(), 0, 1) . '</text></svg>') }}" alt="{{ $member->displayName() }}">
                        @endif
                        <div class="name">{{ $member->displayName() }}</div>
                        <div class="position">{{ $member->position?->name ?? '—' }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="links">
            <a href="{{ route('alumkit.dashboard') }}">Dashboard</a>
            <a href="{{ route('alumkit.committee.index') }}">Manage Committee</a>
        </div>
    </div>
</body>
</html>
