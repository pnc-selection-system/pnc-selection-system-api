<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submission Successful - Interest Assessment</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.5);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes checkmark {
            0% {
                stroke-dashoffset: 100;
            }
            100% {
                stroke-dashoffset: 0;
            }
        }

        @keyframes confetti {
            0% {
                opacity: 0;
                transform: translateY(0) rotate(0deg);
            }
            50% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                transform: translateY(-100px) rotate(720deg);
            }
        }

        .animate-scale-in {
            animation: scaleIn 0.5s ease-out forwards;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
            animation-delay: 0.3s;
            opacity: 0;
        }

        .checkmark-circle {
            animation: scaleIn 0.6s ease-out forwards;
        }

        .checkmark-path {
            stroke-dasharray: 100;
            stroke-dashoffset: 100;
            animation: checkmark 0.8s ease-out 0.5s forwards;
        }

        .confetti-piece {
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 2px;
            animation: confetti 1.5s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <!-- Confetti Container -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden" id="confetti-container"></div>

    <div class="max-w-lg w-full">
        <!-- Success Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
            <!-- Success Icon -->
            <div class="pt-12 pb-6 text-center bg-gradient-to-b from-green-50 to-white relative">
                <!-- Animated Circle -->
                <div class="relative inline-block">
                    <svg class="checkmark-circle w-24 h-24" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="45" fill="none" stroke="#10b981" stroke-width="4" opacity="0.2" />
                        <circle cx="50" cy="50" r="45" fill="none" stroke="#10b981" stroke-width="4" stroke-linecap="round"
                            stroke-dasharray="283" stroke-dashoffset="283"
                            style="animation: checkmark 0.8s ease-out 0.3s forwards;" />
                        <path class="checkmark-path" d="M30 50 L45 65 L70 35" fill="none" stroke="#10b981" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>

                <!-- Confetti -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="confetti-piece bg-blue-400" style="left: 20%; top: 30%; animation-delay: 0.2s;"></div>
                    <div class="confetti-piece bg-purple-400" style="left: 80%; top: 25%; animation-delay: 0.4s;"></div>
                    <div class="confetti-piece bg-pink-400" style="left: 15%; top: 50%; animation-delay: 0.3s;"></div>
                    <div class="confetti-piece bg-yellow-400" style="left: 85%; top: 45%; animation-delay: 0.5s;"></div>
                    <div class="confetti-piece bg-green-400" style="left: 30%; top: 20%; animation-delay: 0.1s;"></div>
                    <div class="confetti-piece bg-indigo-400" style="left: 70%; top: 15%; animation-delay: 0.6s;"></div>
                </div>
            </div>

            <!-- Success Message -->
            <div class="px-8 pb-8 text-center animate-fade-in-up">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Submission Successful!</h1>
                <p class="text-gray-600 mb-8">Thank you for completing the interest assessment. Your information has been recorded successfully.</p>

                <!-- Submission Details -->
                <div class="bg-gray-50 rounded-xl p-6 mb-8 text-left">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Submission Details</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Full Name</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $student->full_name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Gender</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $student->gender }}</dd>
                        </div>
                        @if($student->phone)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Phone</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $student->phone }}</dd>
                        </div>
                        @endif
                        @if($student->school_grade)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">School Grade</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $student->school_grade }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Campaign</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $session->campaign->name ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Session Date</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $session->session_date?->format('M d, Y') ?? 'N/A' }}</dd>
                        </div>
                        @if($student->total_score !== null)
                        <div class="flex justify-between border-t border-gray-200 pt-3">
                            <dt class="text-sm font-medium text-gray-700">Assessment Score</dt>
                            <dd class="text-sm font-bold text-green-600">{{ $student->total_score }}%</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Info Message -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="ml-3 text-sm text-blue-700">
                            Please save your reference number for future inquiries.
                            <br>
                            <span class="font-semibold">Reference #: {{ $student->id }}</span>
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('interest-assessment.show', $session->id) }}"
                        class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Submit Another Response
                    </a>
                    <button onclick="window.print()"
                        class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Confirmation
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-sm text-gray-500 mt-6">PNC Selection System &copy; {{ date('Y') }}</p>
    </div>

</body>
</html>
