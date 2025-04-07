<x-mail::message>

# New Password

Your temporary password is: <span style="background-color: #f0f0f0; padding: 2px 4px; border-radius: 3px; cursor: pointer;" onclick="copyPassword(event)">{{ $pass }}</span>
<p>
    Change it to a stronger password after you login.
</p>
<script>
function copyPassword(event) {
    event.target.style.background = '#cfffdc';
    navigator.clipboard.writeText('{{ $pass }}').then(() => {
        setTimeout(() => {
            event.target.style.background = '#f0f0f0';
        }, 2000);
    });
}
</script>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
