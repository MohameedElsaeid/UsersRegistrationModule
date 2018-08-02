<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Reset Password</title>
    </head>
    <body>

        <p>Dear {{$UserName}},</p>
        <p>
            We have received your request to reset your password.<br/>
            Please Copy this code to complete the reset:
        </p>
        <p>
            Your Code : {{$Code}}<br/>
        </p>
        <p>
            if you need additional assistance, or you did not make this change,<br/>
            please contact sunandsatapp@gmail.com.<br/>
        </p>
        <p>
            Cheers,<br/>
            SunSat Team
        </p>
    </body>
</html>