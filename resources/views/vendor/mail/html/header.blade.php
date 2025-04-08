<tr>
    <td class="header">
    <a href="{{ $url }}" style="display: inline-block;">
    @if (trim($slot) === 'Laravel')
    <img src="{{ asset('img/logo.jpeg') }}" class="logo" alt="Varaid Logo">
    @else
    {{ $slot }}
    @endif
    </a>
    </td>
    </tr>