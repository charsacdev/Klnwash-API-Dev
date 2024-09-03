@component('mail::message')

<div class="email-div">
  <h1>Password Update Confirmation</h1>

  <aside class="aside-1">
    <img src="{{asset('assets/emailicon.png')}}" class="emailicon">
    <br>
    <p>Hi,&nbsp;<b>{{ucwords($username)}}</b><br>
       We confirm that your password has been updated for your account with our laundry company. 
       If you did not initiate the change, 
       please notify us immediately as it may indicate a potential security breach. 
       To confirm your authorization, kindly reply to this email with a simple "yes" or "no." 
       If you have any questions or concerns about your account, we are available to assist you.
    </p>
     <p style="text-align: center">
        We look forward to serving you.Best regards,
    </p>

  </aside>

  <aside class="aside-2">
    <h4>Get the KlnWash app!</h4>
    <p>Get the most of <b>klnwash by</b> installing the
     mobile app. You can log in by using your
     existing email and password
    </p>
    <article>
     <table align="center" border="0" cellpadding="10" cellspacing="10">
       <tr>
         <td>
           <a href="{{env('APPLE_URL')}}">
             <img src="{{asset('assets/apple.png')}}">
           </a>
          </td>
         <td> 
          <a href="{{env('GOOGLE_URL')}}">
           <img src="{{asset('assets/google.png')}}">
           </a>
         </td>
       </tr>
     </table> 
    </article>
 </aside>
 
 <aside class="aside-3">
   <article>
     <table>
      <tr>
        <td><a href="{{env('INSTAGRAM')}}"><img src="{{asset('assets/linkedin.png')}}"></a></td>
        <td><a href="{{env('TWITTER')}}"><img src="{{asset('assets/twitter.png')}}"></a></td>
        <td><a href="{{env('FACEBOOK')}}"><img src="{{asset('assets/facebook.png')}}"></a></td>
      </tr>
     </table>
   </article>
 </aside>

 <aside class="aside-4">
     <img src="{{asset('assets/Vector.png')}}">
     <br>
     <h4>{{ env('APP_BUSINESS') }}</h4>
 </aside>
</div>
@endcomponent
