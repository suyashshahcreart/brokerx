<html>

<body style='font-family: Arial, sans-serif;'>
    <h2>Hello {{ $otpRequest->visitors_name }},</h2>
    <p>Thank you for your inquiry about the property <strong>{{ $tour->name }}</strong>.</p>
    <p>You can now download your document using the link below:</p>
    <p><a href='{{ $link }}'
            style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Download
            Document</a></p>
    <hr style='margin:20px 0;'>
    <p>Regards,<br>Proppik Team</p>
</body>

</html>