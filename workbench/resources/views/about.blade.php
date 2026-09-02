<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $contents->get('hero')?->fields['heading'] ?? 'About' }}</title>
</head>
<body>
    <article>
        <h1>{{ $contents->get('hero')?->fields['heading'] ?? 'About' }}</h1>
        <div>{!! $contents->get('hero')?->fields['body'] ?? '' !!}</div>

        @if (! empty($contents->get('team')?->fields['members']))
            <h2>Team</h2>
            <ul>
                @foreach ($contents->get('team')?->fields['members'] ?? [] as $member)
                    <li>{{ $member['name'] }} — {{ $member['role'] }}</li>
                @endforeach
            </ul>
        @endif
    </article>
</body>
</html>
