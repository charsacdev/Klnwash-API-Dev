@component('mail::message')
<h1 style="text-align:center"> Admin Password Reset Code</h1>

<p style="text-align:justify">
    Hi,&nbsp;<b>{{ucwords($username)}}</b><br>
    We prioritize your account's security and require an authentication code to safeguard your personal data. Kindly input the provided authentication code to finish the registration process.
</p>

@component('mail::button', ['url' => '{{ env('ADMIN_URL')}}/api/admin/newpassword?kln='.base64_encode($email), 'color' => 'green'])
Click to Proceed
@endcomponent



 <p style="text-align: center">
    We look forward to serving you.Best regards,
</p>

Happy Managing,<br>
{{ config('app.name') }}
@endcomponent
