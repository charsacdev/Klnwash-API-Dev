@component('mail::message')
<h1 style="text-align:center"> Admin Password Update Notification</h1>
<p>
    Hi,&nbsp;<b>{{ucwords($username)}}</b><br>
    We confirm that your password has been updated for your account with our administrative portal
    thanks once again for being part of our team.
 </p>
  

Happy Managing,<br>
{{ config('app.name') }}
@endcomponent
