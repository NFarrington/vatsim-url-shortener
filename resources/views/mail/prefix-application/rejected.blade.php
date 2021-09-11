@component('mail::message')

Hi, {{ $name }}!

Thank you for submitting a prefix application for your organization, **{{ $organizationName }}**.

Unfortunately, we have been unable to approve this application at this time. The following reason was given:

---

```
{!! $reason !!}
```

---

If you have any questions or issues, please contact [support@vats.im](mailto:support@vats.im).

Kind regards,<br>
VATS.IM URL Shortener

@endcomponent
