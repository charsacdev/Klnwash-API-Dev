@component('mail::message')
<h1 style="text-align:center"> Welcome To Klnwash Administrative Portal</h1>

<p style="text-align:justify">
Hello ,<b>{{ucwords($username)}}</b>
<br>
You have been selected as an admin of Klnwash to over see the managment of all business related to our services 
in the specified location, please click on the link below to reset your passowrd
</p>

@component('mail::button', ['url' => '{{ env('ADMIN_URL')}}/api/admin/newpassword?kln='.base64_encode($email), 'color' => 'green'])
Reset Password
@endcomponent

Happy Managing,<br>
{{ config('app.name') }}
@endcomponent
