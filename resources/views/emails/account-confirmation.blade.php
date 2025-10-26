<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Confirmation de compte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f4f4f4;
            border-radius: 10px;
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue {{ $user['name'] }}!</h1>
        </div>
        
        <p>Merci de vous être inscrit sur notre plateforme.</p>
        
        <p>Pour activer votre compte, veuillez cliquer sur le lien suivant: </p>

        <p>{{ $confirmationUrl }}</p>

    </div>
</body>
</html>