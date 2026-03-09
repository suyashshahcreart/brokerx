<html>
<body style='font-family: Arial, sans-serif;'>
    <h2>Hello {{ $otpRequest->customer->firstname  }} {{ $otpRequest->customer->lastname }},</h2>
    <p>You’ve received a new Inquiry  for your {{ $tour->name }} property.</p>
    <p><strong>Customer Details:</strong></p>
    <ul>
        <li>Name: {{ $otpRequest->visitors_name }}</li>
        <li>Email: {{ $otpRequest->visitors_email }}</li>
        <li>Mobile: {{ $otpRequest->visitors_mobile }}</li>
    </ul>
    <p>Download link has been shared with the customer.</p>
    <hr>
    <p>Regards,<br>Proppik Team</p>
</body>
</html>