<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Interest Assessment - {{ $session->campaign->name ?? 'Selection Campaign' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <style>
            [x-cloak] { display: none !important; }
        </style>
    @endif

    <style>
        /* Custom animations */
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

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        .animate-slide-in {
            animation: slideIn 0.3s ease-out forwards;
        }

        /* Step transitions */
        .step-enter {
            opacity: 0;
            transform: translateX(20px);
        }

        .step-enter-active {
            opacity: 1;
            transform: translateX(0);
            transition: all 0.3s ease-out;
        }

        .step-leave {
            opacity: 1;
            transform: translateX(0);
        }

        .step-leave-active {
            opacity: 0;
            transform: translateX(-20px);
            transition: all 0.3s ease-out;
        }

        /* Progress bar animation */
        .progress-bar {
            transition: width 0.5s ease-in-out;
        }

        /* Input focus effects */
        .input-focus {
            transition: all 0.2s ease;
        }

        .input-focus:focus {
            transform: scale(1.01);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Button hover effects */
        .btn-primary {
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Card hover effect */
        .card-hover {
            transition: all 0.2s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div x-data="interestAssessment()" x-init="init()" class="min-h-screen">

        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Interest Assessment</h1>
                        <p class="mt-1 text-sm text-gray-600">{{ $session->campaign->name ?? 'Selection Campaign' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">{{ $session->province->name ?? '' }}</p>
                        <p class="text-xs text-gray-500">{{ $session->school->name ?? '' }}</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Progress Bar -->
        <div x-show="!submitSuccess" class="bg-white border-b border-gray-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Step <span x-text="currentStep"></span> of <span x-text="totalSteps"></span></span>
                    <span class="text-sm text-gray-500" x-text="getStepLabel()"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full progress-bar" :style="{ width: progressPercent + '%' }"></div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- AJAX Error Message -->
            <div x-show="submitError" x-transition class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 animate-fade-in-up">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                        <p class="mt-1 text-sm text-red-700" x-text="submitError"></p>
                        <template x-if="Object.keys(validationErrors).length > 0">
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                <template x-for="(msgs, field) in validationErrors" :key="field">
                                    <template x-for="msg in msgs" :key="msg">
                                        <li x-text="msg"></li>
                                    </template>
                                </template>
                            </ul>
                        </template>
                    </div>
                    <button @click="submitError = null; validationErrors = {}" class="ml-auto text-red-400 hover:text-red-600">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            <form @submit.prevent="handleSubmit()">

                <!-- Step 1: Student Information -->
                <div x-show="currentStep === 1 && !submitSuccess" x-transition:enter="step-enter" x-transition:enter-start="step-enter" x-transition:enter-end="step-enter-active" x-transition:leave="step-leave" x-transition:leave-start="step-leave" x-transition:leave-end="step-leave-active">

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden card-hover">
                        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-lg font-semibold text-gray-900">Student Information</h2>
                                    <p class="text-sm text-gray-600">Please provide your personal details</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 space-y-6">
                            <!-- Full Name -->
                            <div class="animate-slide-in" style="animation-delay: 0.1s;">
                                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    x-model="formData.full_name"
                                    required
                                    maxlength="150"
                                    class="input-focus block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400"
                                    placeholder="Enter your full name"
                                >
                            </div>

                            <!-- Gender -->
                            <div class="animate-slide-in" style="animation-delay: 0.2s;">
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Gender <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-3 gap-3">
                                    <template x-for="option in ['Male', 'Female', 'Other']" :key="option">
                                        <label
                                            class="relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all duration-200"
                                            :class="formData.gender === option ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-200 hover:border-gray-300 bg-white'"
                                        >
                                            <input
                                                type="radio"
                                                name="gender"
                                                :value="option"
                                                x-model="formData.gender"
                                                class="sr-only"
                                                required
                                            >
                                            <span class="text-sm font-medium" :class="formData.gender === option ? 'text-blue-700' : 'text-gray-700'" x-text="option"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="animate-slide-in" style="animation-delay: 0.3s;">
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    x-model="formData.phone"
                                    maxlength="30"
                                    class="input-focus block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400"
                                    placeholder="Enter your phone number"
                                >
                            </div>

                            <!-- School Grade -->
                            <div class="animate-slide-in" style="animation-delay: 0.4s;">
                                <label for="school_grade" class="block text-sm font-medium text-gray-700 mb-2">
                                    School Grade / Year
                                </label>
                                <select
                                    id="school_grade"
                                    name="school_grade"
                                    x-model="formData.school_grade"
                                    class="input-focus block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900"
                                >
                                    <option value="">Select your grade</option>
                                    <option value="Grade 9">Grade 9</option>
                                    <option value="Grade 10">Grade 10</option>
                                    <option value="Grade 11">Grade 11</option>
                                    <option value="Grade 12">Grade 12</option>
                                    <option value="Grade 12 (Graduated)">Grade 12 (Graduated)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Assessment Questions (if assessment form exists) -->
                @if($assessmentForm)
                <div x-show="currentStep === 2 && !submitSuccess" x-transition:enter="step-enter" x-transition:enter-start="step-enter" x-transition:enter-end="step-enter-active" x-transition:leave="step-leave" x-transition:leave-start="step-leave" x-transition:leave-end="step-leave-active">

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden card-hover">
                        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-pink-50">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-lg font-semibold text-gray-900">{{ $assessmentForm->name }}</h2>
                                    <p class="text-sm text-gray-600">Please answer the following questions</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 space-y-8">
                            @foreach($assessmentForm->fields() as $index => $field)
                                <div class="animate-slide-in" style="animation-delay: {{ ($index + 1) * 0.1 }}s;">
                                    <label for="field_{{ $field['key'] }}" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ $field['label'] ?? $field['key'] }}
                                        @if($field['rules']['required'] ?? false)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>

                                    @if($field['type'] === 'text' || $field['type'] === 'textarea')
                                        <textarea
                                            id="field_{{ $field['key'] }}"
                                            name="{{ $field['key'] }}"
                                            x-model="formData.{{ $field['key'] }}"
                                            {{ ($field['rules']['required'] ?? false) ? 'required' : '' }}
                                            rows="3"
                                            maxlength="{{ $field['rules']['max'] ?? 500 }}"
                                            class="input-focus block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-gray-900 placeholder-gray-400"
                                            placeholder="Enter your answer"
                                        ></textarea>

                                    @elseif($field['type'] === 'number' || $field['type'] === 'rating')
                                        <input
                                            type="number"
                                            id="field_{{ $field['key'] }}"
                                            name="{{ $field['key'] }}"
                                            x-model="formData.{{ $field['key'] }}"
                                            {{ ($field['rules']['required'] ?? false) ? 'required' : '' }}
                                            min="{{ $field['rules']['min'] ?? 0 }}"
                                            max="{{ $field['rules']['max'] ?? 10 }}"
                                            step="{{ $field['type'] === 'rating' ? 1 : 'any' }}"
                                            class="input-focus block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-gray-900 placeholder-gray-400"
                                            placeholder="Enter a number"
                                        >
                                        @if($field['type'] === 'rating')
                                            <p class="mt-1 text-xs text-gray-500">Rate from {{ $field['rules']['min'] ?? 0 }} to {{ $field['rules']['max'] ?? 10 }}</p>
                                        @endif

                                    @elseif(in_array($field['type'], ['select', 'radio']))
                                        <div class="space-y-2">
                                            @if($field['type'] === 'select')
                                                <select
                                                    id="field_{{ $field['key'] }}"
                                                    name="{{ $field['key'] }}"
                                                    x-model="formData.{{ $field['key'] }}"
                                                    {{ ($field['rules']['required'] ?? false) ? 'required' : '' }}
                                                    class="input-focus block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-gray-900"
                                                >
                                                    <option value="">Select an option</option>
                                                    @foreach($field['options'] ?? [] as $option)
                                                        <option value="{{ $option }}">{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    @foreach($field['options'] ?? [] as $option)
                                                        <label
                                                            class="relative flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all duration-200"
                                                            :class="formData.{{ $field['key'] }} === '{{ $option }}' ? 'border-purple-500 bg-purple-50 ring-2 ring-purple-200' : 'border-gray-200 hover:border-gray-300 bg-white'"
                                                        >
                                                            <input
                                                                type="radio"
                                                                name="{{ $field['key'] }}"
                                                                value="{{ $option }}"
                                                                x-model="formData.{{ $field['key'] }}"
                                                                {{ ($field['rules']['required'] ?? false) ? 'required' : '' }}
                                                                class="sr-only"
                                                            >
                                                            <span class="text-sm" :class="formData.{{ $field['key'] }} === '{{ $option }}' ? 'text-purple-700 font-medium' : 'text-gray-700'">{{ $option }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                    @elseif($field['type'] === 'checkbox')
                                        <div class="space-y-2">
                                            @foreach($field['options'] ?? [] as $option)
                                                <label
                                                    class="relative flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all duration-200"
                                                    :class="formData.{{ $field['key'] }} && formData.{{ $field['key'] }}.includes('{{ $option }}') ? 'border-purple-500 bg-purple-50 ring-2 ring-purple-200' : 'border-gray-200 hover:border-gray-300 bg-white'"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        name="{{ $field['key'] }}[]"
                                                        value="{{ $option }}"
                                                        x-model="formData.{{ $field['key'] }}"
                                                        class="sr-only"
                                                    >
                                                    <span class="text-sm" :class="formData.{{ $field['key'] }} && formData.{{ $field['key'] }}.includes('{{ $option }}') ? 'text-purple-700 font-medium' : 'text-gray-700'">{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>

                                    @else
                                        <input
                                            type="text"
                                            id="field_{{ $field['key'] }}"
                                            name="{{ $field['key'] }}"
                                            x-model="formData.{{ $field['key'] }}"
                                            {{ ($field['rules']['required'] ?? false) ? 'required' : '' }}
                                            maxlength="{{ $field['rules']['max'] ?? 255 }}"
                                            class="input-focus block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-gray-900 placeholder-gray-400"
                                            placeholder="Enter your answer"
                                        >
                                    @endif

                                    @if(isset($field['description']))
                                        <p class="mt-1 text-xs text-gray-500">{{ $field['description'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Step 3: Review & Submit -->
                <div x-show="currentStep === {{ $assessmentForm ? 3 : 2 }} && !submitSuccess" x-transition:enter="step-enter" x-transition:enter-start="step-enter" x-transition:enter-end="step-enter-active" x-transition:leave="step-leave" x-transition:leave-start="step-leave" x-transition:leave-end="step-leave-active">

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden card-hover">
                        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-lg font-semibold text-gray-900">Review Your Information</h2>
                                    <p class="text-sm text-gray-600">Please verify your details before submitting</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <!-- Student Info Summary -->
                            <div class="mb-6">
                                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Personal Information</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <dt class="text-xs font-medium text-gray-500">Full Name</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900" x-text="formData.full_name || '-'"></dd>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <dt class="text-xs font-medium text-gray-500">Gender</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900" x-text="formData.gender || '-'"></dd>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <dt class="text-xs font-medium text-gray-500">Phone</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900" x-text="formData.phone || '-'"></dd>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <dt class="text-xs font-medium text-gray-500">School Grade</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900" x-text="formData.school_grade || '-'"></dd>
                                    </div>
                                </dl>
                            </div>

                            @if($assessmentForm)
                            <!-- Assessment Answers Summary -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Assessment Answers</h3>
                                <dl class="space-y-3">
                                    @foreach($assessmentForm->fields() as $field)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <dt class="text-xs font-medium text-gray-500">{{ $field['label'] ?? $field['key'] }}</dt>
                                            <dd class="mt-1 text-sm font-semibold text-gray-900" x-text="formatAnswer('{{ $field['key'] }}', '{{ $field['type'] }}')"></dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Success State -->
                <div x-show="submitSuccess" x-transition class="mt-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden animate-fade-in-up">
                        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-lg font-semibold text-green-800">Assessment Submitted Successfully!</h2>
                                    <p class="text-sm text-green-600">Your response has been recorded.</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 text-center">
                            <div class="mb-4">
                                <svg class="mx-auto h-16 w-16 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-gray-600 mb-6">Thank you for completing the interest assessment. Your responses have been saved successfully.</p>
                            <button
                                type="button"
                                @click="resetForm()"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Submit Another Response
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div x-show="!submitSuccess" class="mt-8 flex items-center justify-between">
                    <button
                        type="button"
                        x-show="currentStep > 1"
                        @click="prevStep()"
                        class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </button>

                    <div class="flex-1"></div>

                    <button
                        type="button"
                        x-show="currentStep < totalSteps"
                        @click="nextStep()"
                        class="btn-primary inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Next Step
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <button
                        type="submit"
                        x-show="currentStep === totalSteps && !submitSuccess"
                        :disabled="isSubmitting"
                        class="btn-primary inline-flex items-center px-8 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <template x-if="isSubmitting">
                            <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <template x-if="!isSubmitting">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                        <span x-text="isSubmitting ? 'Submitting...' : 'Submit Assessment'"></span>
                    </button>
                </div>
            </form>
        </main>

        <!-- Footer -->
        <footer class="mt-auto py-6 border-t border-gray-200 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-sm text-gray-500">PNC Selection System &copy; {{ date('Y') }}</p>
            </div>
        </footer>
    </div>

    <script>
        function interestAssessment() {
            return {
                currentStep: 1,
                totalSteps: {{ $assessmentForm ? 3 : 2 }},
                formData: {
                    full_name: '{{ old('full_name', '') }}',
                    gender: '{{ old('gender', '') }}',
                    phone: '{{ old('phone', '') }}',
                    school_grade: '{{ old('school_grade', '') }}',
                    @if($assessmentForm)
                        @foreach($assessmentForm->fields() as $field)
                            @if($field['type'] === 'checkbox')
                                {{ $field['key'] }}: {!! json_encode(old($field['key'], [])) !!},
                            @else
                                {{ $field['key'] }}: '{{ old($field['key'], '') }}',
                            @endif
                        @endforeach
                    @endif
                },

                init() {
                    // Check for validation errors and jump to the appropriate step
                    @if($errors->any())
                        @if($assessmentForm && $errors->hasAny(collect($assessmentForm->fields())->pluck('key')->toArray()))
                            this.currentStep = 2;
                        @endif
                    @endif
                },

                get progressPercent() {
                    return (this.currentStep / this.totalSteps) * 100;
                },

                getStepLabel() {
                    const labels = {
                        1: 'Personal Information',
                        @if($assessmentForm)
                            2: 'Assessment Questions',
                            3: 'Review & Submit'
                        @else
                            2: 'Review & Submit'
                        @endif
                    };
                    return labels[this.currentStep] || '';
                },

                nextStep() {
                    if (this.currentStep < this.totalSteps) {
                        this.currentStep++;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                prevStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                formatAnswer(key, type) {
                    const value = this.formData[key];
                    if (value === undefined || value === null || value === '') {
                        return '-';
                    }
                    if (Array.isArray(value)) {
                        return value.length > 0 ? value.join(', ') : '-';
                    }
                    return String(value);
                },

                isSubmitting: false,
                submitSuccess: false,
                submitError: null,
                validationErrors: {},

                handleSubmit() {
                    if (this.currentStep !== this.totalSteps || this.isSubmitting) return;

                    this.isSubmitting = true;
                    this.submitError = null;
                    this.validationErrors = {};

                    // Build the payload
                    const payload = {
                        full_name: this.formData.full_name,
                        gender: this.formData.gender,
                        phone: this.formData.phone || null,
                        school_grade: this.formData.school_grade || null,
                    };

                    // Add assessment answers
                    @if($assessmentForm)
                        @foreach($assessmentForm->fields() as $field)
                            @if($field['type'] === 'checkbox')
                                payload['{{ $field['key'] }}'] = this.formData['{{ $field['key'] }}'];
                            @else
                                if (this.formData['{{ $field['key'] }}'] !== '' && this.formData['{{ $field['key'] }}'] !== null && this.formData['{{ $field['key'] }}'] !== undefined) {
                                    payload['{{ $field['key'] }}'] = this.formData['{{ $field['key'] }}'];
                                }
                            @endif
                        @endforeach
                    @endif

                    fetch('/api/interest-assessment/{{ $session->id }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify(payload),
                    })
                    .then(response => response.json().then(data => ({ status: response.status, data })))
                    .then(({ status, data }) => {
                        if (status >= 200 && status < 300) {
                            this.submitSuccess = true;
                            this.isSubmitting = false;
                        } else if (status === 422 && data.errors) {
                            this.validationErrors = data.errors;
                            this.submitError = 'Please fix the validation errors below.';
                            this.isSubmitting = false;
                            // Jump to step 1 if name/gender errors
                            if (data.errors.full_name || data.errors.gender) {
                                this.currentStep = 1;
                            }
                        } else {
                            this.submitError = data.message || 'Something went wrong. Please try again.';
                            this.isSubmitting = false;
                        }
                    })
                    .catch(err => {
                        this.submitError = 'Network error. Please check your connection and try again.';
                        this.isSubmitting = false;
                    });
                },

                resetForm() {
                    this.formData = {
                        full_name: '',
                        gender: '',
                        phone: '',
                        school_grade: '',
                        @if($assessmentForm)
                            @foreach($assessmentForm->fields() as $field)
                                @if($field['type'] === 'checkbox')
                                    {{ $field['key'] }}: [],
                                @else
                                    {{ $field['key'] }}: '',
                                @endif
                            @endforeach
                        @endif
                    };
                    this.currentStep = 1;
                    this.submitSuccess = false;
                    this.submitError = null;
                    this.validationErrors = {};
                },


            }
        }
    </script>
</body>
</html>
