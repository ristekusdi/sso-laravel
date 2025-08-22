<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Demo SSO Laravel - RistekUSDI</title>
</head>

<body>
    <h1>SSO Web Demo</h1>
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
    @if (auth('imissu-web')->user()->hasPermission('user:view'))
    <p>This user has permission user:view</p>
    @else
    <p>This user doesn't have permission user:view</p>
    @endif

    @if (auth('imissu-web')->user()->hasRole('Pegawai'))
    <p>This user has role Pegawai</p>
    @else
    <p>This user doesn't have role Pegawai</p>
    @endif
    <form action="">
        <select name="roles" id="roles">
            <option value="0">Roles</option>
            @if (auth('imissu-web')->user()->roles)
            @foreach (auth('imissu-web')->user()->roles as $role)
            <option value="{{ json_encode($role) }}" {{ (auth('imissu-web')->user()->role->name == $role->name) ? 'selected' : '' }}>{{ $role->name }}</option>
            @endforeach
            @endif
        </select>
        <input type="hidden" name="home_url" value="{{ url() }}">
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

    <input type="hidden" name="url_change_role" value="{{ url('/web-session/change-role') }}">

    <script>
        document.getElementById('roles').onchange = function(e) {
            const value = e.target.value;
            const url = document.querySelector('input[name="url_change_role"]').value;
            const home_url = document.querySelector('input[name="home_url"]').value;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(url, {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    method: 'POST',
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        role: value
                    })
                })
                .then(response => {
                    if (response.ok) {
                        window.location.href = home_url;
                    } else {
                        switch (response.status) {
                            case 401:
                                throw new Error("Sesi login Anda sudah habis! Silakan login kembali.");
                                break;
                            case 403:
                                throw new Error("Tidak dapat mengubah peran aktif! Silakan login kembali.");
                            default:
                                throw new Error("Terjadi kesalahan sistem! Silakan login kembali.");
                                break;
                        }
                    }
                })
                .then(err => {
                    alert(err.message);
                    window.location.reload();
                });
        }
    </script>
</body>
</html>