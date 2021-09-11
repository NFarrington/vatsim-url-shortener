@component('mail::message')

Hi, {{ $name }}!

Thank you for submitting a prefix application for your organization, **{{ $organizationName }}**.

Your application has now been approved, and you can start using the **{{ $prefix }}** prefix straight away. To use the prefix:

1. Visit <https://vats.im/platform/urls/create>. You should see a box available to select a prefix.
2. Select the **{{ $prefix }}** prefix from the prefix dropdown menu.
3. Select the **{{ $organizationName }}** organization from the organization dropdown menu.

In order to create URLs with the **{{ $prefix }}** prefix, you must always select the corresponding **{{ $organizationName }}** organization.

If you have any questions or issues, please contact [support@vats.im](mailto:support@vats.im).

Kind regards,<br>
VATS.IM URL Shortener

@endcomponent
