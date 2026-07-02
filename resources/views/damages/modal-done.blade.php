<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><title>OK</title></head>
<body style="font-family:sans-serif; text-align:center; padding-top:40px; color:#555;">
    <p>✔ {{ trans('admin/damages/message.update.success') }}</p>
    <script nonce="{{ csrf_token() }}">
        // Tell the parent window (the damages list) to close the modal and refresh the table.
        if (window.parent && window.parent !== window) {
            window.parent.postMessage('damage-saved', '*');
        } else {
            window.location.href = '{{ route('damages.list') }}';
        }
    </script>
</body>
</html>
