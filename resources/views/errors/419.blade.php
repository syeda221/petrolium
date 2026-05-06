<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Expired - 419</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="bg-[#f8fafc] flex items-center justify-center min-h-screen p-6 overflow-hidden relative">
    <!-- Decorative Elements -->
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-pulse"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-100 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-pulse delay-700"></div>

    <div class="max-w-2xl w-full text-center relative z-10">
        <!-- Icon/Illustration Section -->
        <div class="mb-8 flex justify-center">
            <div class="relative animate-float">
                <div class="w-32 h-32 bg-white rounded-3xl shadow-xl flex items-center justify-center relative z-10 glass">
                    <svg class="w-16 h-16 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="absolute -bottom-4 -right-4 w-12 h-12 bg-indigo-600 rounded-2xl shadow-lg flex items-center justify-center transform rotate-12">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Text Content -->
        <h1 class="text-9xl font-extrabold text-indigo-900/10 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 select-none tracking-tighter">419</h1>
        <h2 class="text-4xl md:text-5xl font-extrabold text-slate-800 mb-4 tracking-tight">Page Expired</h2>
        <p class="text-lg text-slate-600 mb-10 max-w-md mx-auto leading-relaxed">
            Your session has expired due to inactivity. For security reasons, please refresh the page and try again.
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button onclick="window.location.reload()" class="w-full sm:w-auto px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-2xl shadow-lg shadow-indigo-200 transition-all duration-300 transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh Page
            </button>
            <button onclick="window.history.back()" class="w-full sm:w-auto px-8 py-4 bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-2xl border border-slate-200 shadow-sm transition-all duration-300 transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Go Back
            </button>
        </div>

        <!-- Footer -->
        <p class="mt-12 text-slate-400 text-sm font-medium">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>
</body>
</html>
