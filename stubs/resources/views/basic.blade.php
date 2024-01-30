<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Demo SSO Laravel - Basic</title>
</head>
<body>
    <h1>SSO Web Demo Basic</h1>
    <a href="{{ route('sso.web.logout') }}">Log out</a>
    <p>List of client roles belongs to {{ auth('imissu-web')->user()->name }}</p>
    <ul>
        @foreach (auth('imissu-web')->user()->client_roles as $role)
            <li>{{ $role }}</li>
        @endforeach
    </ul>
    <p><strong>Available attributes</strong></p>
    <table width="100%" style="border: 1px solid black;">
        <thead>
            <th style="border: 1px solid black;">Attribute Key</th>
            <th style="border: 1px solid black;">Attribute Value</th>
        </thead>
        <tbody>
            @foreach (auth('imissu-web')->user()->getAttributes() as $key => $value)
            <tr>
                <td style="border: 1px solid black;">{{ $key }}</td>
                <td style="border: 1px solid black;">
                    @if (is_array($value))
                        @if (in_array($key, ['realm_access', 'resource_access']))
                            {{ json_encode($value) }}
                        @else
                            {{ collect($value)->implode(', ') }}
                        @endif
                    @else
                        {{ $value }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>