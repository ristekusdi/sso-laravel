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
    <h1>Hello</h1>
    <p>We can do it together. :)</p>
    <a href="{{ route('sso.web.logout') }}">Log out</a>
    <p>Berikut daftar peran yang dimiliki oleh {{ auth('imissu-web')->user()->name }}</p>
    <ul>
        @foreach (auth('imissu-web')->user()->roles as $role)
            <li>{{ $role }}</li>
        @endforeach
    </ul>
    <p>Peran aktif: {{ auth('imissu-web')->user()->role_active }}</p>
    <p>Daftar permission: </p>
    <ul>
        @foreach (auth('imissu-web')->user()->role_active_permissions as $perm)
            <li>{{ $perm }}</li>
        @endforeach
    </ul>
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
        <select name="roles" id="roles-combo">
            <option value="0">Daftar Peran</option>
            @foreach (auth('imissu-web')->user()->roles() as $role)
                <option value="{{ $role }}" {{ (auth('imissu-web')->user()->role_active == $role) ? 'selected' : '' }}>{{ $role }}</option>
            @endforeach
        </select>
        <input type="hidden" name="current_url" value="{{ url()->current() }}">
    </form>
    <p>Atribut yang bisa di akses sebagai berikut</p>
    <table width="100%" style="border: 1px solid black;">
        <thead>
            <th style="border: 1px solid black;">Atribut</th>
            <th style="border: 1px solid black;">Nilai atribut</th>
        </thead>
        <tbody>
            @foreach (auth('imissu-web')->user()->getAttributes() as $key => $value)
            <tr>
                <td style="border: 1px solid black;">{{ $key }}</td>
                <td style="border: 1px solid black;">
                    @if (is_array($value))
                        {{ collect($value)->implode(', ') }}
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