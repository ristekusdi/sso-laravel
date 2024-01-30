<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Demo SSO Laravel - Advance</title>
</head>
<body>
    <h1>SSO Web Demo Advance</h1>
    <a href="{{ route('sso.web.logout') }}">Log out</a>
    <p>List of client roles belongs to {{ auth('imissu-web')->user()->name }}</p>
    <ul>
        @foreach (auth('imissu-web')->user()->client_roles as $role)
            <li>{{ $role }}</li>
        @endforeach
    </ul>
    <p>Active role / current role: {{ auth('imissu-web')->user()->role->name ?? '??' }}</p>
    <p>Permissions:</p>
    @php
        $permissions = auth('imissu-web')->user()->role->permissions ?? [];
    @endphp
    @if (!empty($permissions))
    <ul>
        @foreach ($permissions as $perm)
            <li>{{ $perm }}</li>
        @endforeach
    </ul>
    @else
        <p><strong>No permissions available</strong></p>
    @endif
    @if (auth('imissu-web')->user()->hasPermission('manage-settings'))
    <p>This user has permission manage-settings</p>
    @else
    <p>This user doesn't have permission manage-settings</p>
    @endif

    @if (auth('imissu-web')->user()->hasRole('Developer'))
    <p>This user has role Developer</p>
    @else
    <p>This user doesn't have role Developer</p>
    @endif
    <form action="">
        <select name="roles" id="roles">
            <option value="0">Roles</option>
            @foreach (auth('imissu-web')->user()->roles as $role)
                <option value="{{ json_encode($role) }}" {{ (auth('imissu-web')->user()->role->name == $role->name) ? 'selected' : '' }}>{{ $role->name }}</option>
            @endforeach
        </select>
        <input type="hidden" name="current_url" value="{{ url()->current() }}">
    </form>
    <p>Available attributes:</p>
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
                        @if (in_array($key, ['roles', 'realm_access', 'resource_access']))
                            {{ json_encode($value) }}
                        @else
                            {{ collect($value)->implode(', ') }}
                        @endif
                    @elseif (is_object($value))
                        {{ json_encode($value) }}
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