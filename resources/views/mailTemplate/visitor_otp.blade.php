<html>
 <head>
    <title>Your OTP code - Prop Pik Globel</title>
 </head>
<body style='font-family: Arial, sans-serif;'>
    <h2>Hello {{$otpRequest->visitors_name}},</h2>
    <p>Your OTP code is:</p>
    <h3 style='background: #f0f0f0; padding: 10px; display: inline-block;'>{{$otp}}</h3>
    <p>This OTP is valid for 5 minutes only.</p>
    <p><strong>Please do not share this OTP with anyone.</strong></p>
    <hr>
    <p>Regards,<br>Proppik Team</p>
</body>
</html>