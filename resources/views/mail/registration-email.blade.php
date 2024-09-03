@component('mail::message')

<div class="email-div">
  <h1>Registration Successful with KLN Wash</h1>

  <aside class="aside-1">
    <img src="{{asset('assets/emailicon.png')}}" class="emailicon">
    <br>
    <p>Hi,&nbsp;<b>{{ucwords($username)}}</b><br>
      We are delighted to inform you that your registration with KLN Wash was been successful. Welcome to our family of satisfied customers.
      <br>
      <b class="congrats">Congratulations!</b>
    </p>
    <button class="button-register">
      Get Started
    </button>
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
