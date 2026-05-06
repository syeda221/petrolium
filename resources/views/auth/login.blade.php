<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Modern Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Modern Gradient Background */
            background: linear-gradient(135deg, #86b3aa 0%, #4a766e 100%);
            padding: 20px;
        }

        .container {
            position: relative;
            max-width: 450px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .forms .form-content .title {
            position: relative;
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }

        /* Animated Underline for Title */
        .forms .form-content .title:before {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -8px;
            transform: translateX(-50%);
            height: 4px;
            width: 50px;
            background: #86b3aa;
            border-radius: 2px;
        }

        .forms .form-content .input-boxes {
            margin-top: 20px;
        }

        .forms .form-content .input-box {
            display: flex;
            align-items: center;
            height: 55px;
            width: 100%;
            margin: 20px 0;
            position: relative;
        }

        /* Improved Input Fields */
        .form-content .input-box input {
            height: 100%;
            width: 100%;
            outline: none;
            border: 2px solid #eee;
            border-radius: 12px;
            padding: 0 50px;
            font-size: 15px;
            font-weight: 400;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }

        .form-content .input-box input:focus {
            border-color: #86b3aa;
            background: #fff;
            box-shadow: 0 0 10px rgba(134, 179, 170, 0.2);
        }

        /* Icons Styling */
        .form-content .input-box i {
            position: absolute;
            left: 18px;
            color: #86b3aa;
            font-size: 18px;
            z-index: 10;
        }

        /* Animated Button */
        .forms .form-content .button {
            margin-top: 35px;
        }

        .forms .form-content .button input {
            color: #fff;
            background: #86b3aa;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(134, 179, 170, 0.4);
        }

        .forms .form-content .button input:hover {
            background: #6e9991;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(134, 179, 170, 0.6);
        }

        .forms .form-content .button input:active {
            transform: translateY(0);
        }

        /* Alert Styling */
        .alert {
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
            border: 1px solid #f5c2c7;
        }

        /* Simple Footer Text */
        .login-text {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #666;
        }

        .login-text a {
            color: #86b3aa;
            text-decoration: none;
            font-weight: 600;
        }

        .login-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="forms">
            <div class="form-content">
                <div class="login-form">
                    <div class="title">Welcome Back</div>
                    
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <p><i class="fas fa-exclamation-circle"></i> {{ $error }}</p>
                            @endforeach
                        </div>
                        @endif

                        <div class="input-boxes">
                            <div class="input-box">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required autofocus>
                            </div>

                            <div class="input-box">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" placeholder="Enter your password" required>
                            </div>

                            <div class="button input-box">
                                <input type="submit" value="Login Now">
                            </div>

                            <div class="login-text">
                                Don't have an account? <a href="#">Signup now</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>