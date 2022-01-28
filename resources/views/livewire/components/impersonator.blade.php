<div
    x-data
    x-on:impersonation-impersonate-user.window="$wire.impersonate($event.detail)"
    x-on:impersonation-impersonate-organization.window="$wire.impersonateOrganization($event.detail)"
    x-on:impersonation-impersonate-out.window="$wire.clearImpersonate()"
>

</div>
