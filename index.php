<?php
session_start();
error_reporting(0);
include('library/includes/config.php'); 

// Color definitions are kept for the footer and visual consistency
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Library Management System | Home</title>
    <link href="library/assets/css/style.css" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-indigo': '#4338CA', 
                        'accent-orange': '#F59E0B', 
                        'info-blue': '#3B82F6',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col antialiased">
    
    <div class="flex-grow py-24 px-4 sm:px-6 lg:px-8 bg-primary-indigo/10 flex items-center justify-center">
        <div class="max-w-xl mx-auto text-center p-8 bg-white rounded-xl shadow-2xl">
            
            <h1 class="text-5xl font-extrabold text-primary-indigo mb-4">
                📚 Modern Library System
            </h1>
            <p class="text-lg text-gray-700 mb-8">
                Welcome! Please log in to access your dashboard, book resources, and administrative tools.
            </p>

            <a href="library/login.php" class="inline-flex items-center justify-center w-full sm:w-auto px-10 py-3 border border-transparent text-base font-bold rounded-lg shadow-md text-white bg-info-blue hover:bg-blue-600 transition duration-300 transform hover:scale-105">
                Go to Login Page →
            </a>
            
            <p class="mt-6 text-sm text-gray-500">
                <a href="library/signup.php" class="text-accent-orange hover:text-orange-600 font-medium">New User? Register here.</a>
            </p>

        </div>
    </div>
    <?php include('library/includes/footer.php');?>
    
</body>
</html>