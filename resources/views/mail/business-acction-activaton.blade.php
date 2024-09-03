@component('mail::panel')
Request for Business Profile
@endcomponent
@component('mail::message')
<p>Dear&nbsp;<b>{{ucwords($businessName)}}</b> </p>

<p>We hope this email finds you well. We are writing to request your business profile 
    as we plan to potentially partnering with your laundry company.
</p>
 
<p>We understand that you are a reputable and well-established laundry company. 
    We would like to learn more about your company's mission, values, and services, 
    as well as your experience in the industry.
</p>
 
<p>If possible, please provide us with a business profile that includes the following information:</p>
 
<ul>
    <li>Company name and contact information</li>
    <li>Overview of your laundry services and equipment</li>
    <li>Experience and qualifications of your staff</li>
    <li>Customer reviews and testimonials</li>
    <li>Pricing and packages</li>
    <li>Any additional information that you think would be relevant</li>
    <li>We appreciate your time and consideration in providing us with this information.</li>
</ul>
 
<p>Thank you for your attention to this matter, and we hope to hear from you soon.</p>

 
Best regards,<br>
<b>{{ucwords($username)}}</b><br>
<br>
{{ config('app.name') }}
@endcomponent
