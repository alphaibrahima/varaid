<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VARAID - Site en construction</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #4dabf7;
            --text-color: #333;
            --light-color: #f8f9fa;
            --font-main: 'Poppins', sans-serif;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-main);
            background-color: var(--light-color);
            color: var(--text-color);
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 20px;
        }
        
        .coming-soon {
            text-align: center;
            padding: 40px 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
        }
        
        .logo {
            max-width: 150px;
            margin-bottom: 30px;
        }
        
        h1 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            color: #555;
        }
        
        .countdown {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 40px 0;
        }
        
        .countdown-item {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            border-radius: 8px;
            min-width: 80px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .countdown-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }
        
        .countdown-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .newsletter {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .newsletter input {
            padding: 12px;
            width: 70%;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            font-size: 1rem;
        }
        
        .newsletter button {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        
        .newsletter button:hover {
            background-color: #3d91d4;
        }
        
        .social-links {
            margin: 30px 0;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: var(--primary-color);
            transition: transform 0.3s;
            text-decoration: none;
            font-size: 24px;
        }
        
        .social-links a:hover {
            transform: translateY(-3px);
        }
        
        footer {
            text-align: center;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            width: 100%;
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }
            
            .countdown {
                flex-wrap: wrap;
            }
            
            .newsletter input {
                width: 60%;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="coming-soon">
            <!--<img src="/api/placeholder/150/150" alt="VARAID Logo" class="logo">-->
            <h1>Notre site arrive bientôt</h1>
            <p>Nous travaillons activement sur la nouvelle version de notre plateforme pour la réservation d'agneaux pour l'Aïd Al Adha. Revenez bientôt pour découvrir notre nouvelle interface et nos fonctionnalités améliorées.</p>
            
            <div class="countdown">
                <div class="countdown-item">
                    <span class="countdown-number" id="days">00</span>
                    <span class="countdown-label">Jours</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="hours">00</span>
                    <span class="countdown-label">Heures</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="minutes">00</span>
                    <span class="countdown-label">Minutes</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="seconds">00</span>
                    <span class="countdown-label">Secondes</span>
                </div>
            </div>
            
            <p>Revenez nous voir dans quelques jours!</p>
            
            <div class="social-links">
                <a href="#">📱</a>
                <a href="#">📧</a>
                <a href="#">📞</a>
                <a href="#">💬</a>
            </div>
            
            <p>Pour toute question, n'hésitez pas à nous contacter: <br>
            <strong>Email:</strong> varaid.contact@gmail.com | 
            <strong>Téléphone:</strong>06 52 32 32 79</p>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 VARAID. Tous droits réservés.</p>
    </footer>

    <script>
        // Set the date we're counting down to (4 days from now)
        const countDownDate = new Date();
        countDownDate.setDate(countDownDate.getDate() + 4);
        
        // Update the countdown every 1 second
        const x = setInterval(function() {
            // Get today's date and time
            const now = new Date().getTime();
            
            // Find the distance between now and the countdown date
            const distance = countDownDate - now;
            
            // Calculate time units
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Display the results
            document.getElementById("days").innerHTML = days.toString().padStart(2, '0');
            document.getElementById("hours").innerHTML = hours.toString().padStart(2, '0');
            document.getElementById("minutes").innerHTML = minutes.toString().padStart(2, '0');
            document.getElementById("seconds").innerHTML = seconds.toString().padStart(2, '0');
            
            // If the countdown is finished, display a message
            if (distance < 0) {
                clearInterval(x);
                document.getElementById("days").innerHTML = "00";
                document.getElementById("hours").innerHTML = "00";
                document.getElementById("minutes").innerHTML = "00";
                document.getElementById("seconds").innerHTML = "00";
            }
        }, 1000);
        
        // No form handling needed
    </script>
</body>
</html>