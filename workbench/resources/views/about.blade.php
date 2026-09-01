<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $hero['heading'] ?? 'About' }}</title>
</head>
<body>
    <article>
        <h1>{{ $hero['heading'] ?? 'About' }}</h1>
        <div>{!! $hero['body'] ?? '' !!}</div>

        @if (! empty($team['members']))
            <h2>Team</h2>
            <ul>
                @foreach ($team['members'] as $member)
                    <li>{{ $member['name'] }} — {{ $member['role'] }}</li>
                @endforeach
            </ul>
        @endif
    </article>
</body>
</html>
