@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50 py-12 px-4">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900">Vehicle Check-In</h1>
                <p class="text-gray-600 mt-2">Simulate RFID scan or manual entry</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <!-- Alerts -->
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="text-red-800">
                            <p class="font-semibold">Validation Error!</p>
                            <ul class="list-disc list-inside mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="text-green-800">
                            <p class="font-semibold">Success!</p>
                            <p class="mt-1">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Form -->
                <form action="{{ route('parking.check-in') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- RFID UID -->
                    <div>
                        <label for="rfid_uid" class="block text-sm font-medium text-gray-900">RFID UID *</label>
                        <p class="text-gray-600 text-xs mt-1">Unique identifier for the RFID tag (simulate RFID scan)</p>
                        <input type="text" id="rfid_uid" name="rfid_uid" value="{{ old('rfid_uid') }}"
                            placeholder="e.g., 550E8400E29B41D4"
                            class="mt-2 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition @error('rfid_uid') border-red-500 @enderror"
                            required autofocus />
                        @error('rfid_uid')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Vehicle Number -->
                    <div>
                        <label for="vehicle_number" class="block text-sm font-medium text-gray-900">Vehicle Number
                            (Optional)</label>
                        <p class="text-gray-600 text-xs mt-1">License plate or vehicle identifier</p>
                        <input type="text" id="vehicle_number" name="vehicle_number" value="{{ old('vehicle_number') }}"
                            placeholder="e.g., ABC-1234"
                            class="mt-2 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition @error('vehicle_number') border-red-500 @enderror" />
                        @error('vehicle_number')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-blue-900 text-sm">
                            <strong>Parking Duration:</strong> 60 minutes from check-in<br>
                            <strong>Expiry Time:</strong> Will be automatically calculated
                        </p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4 pt-4">
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                            Check In Vehicle
                        </button>
                        <a href="{{ route('parking.dashboard') }}"
                            class="flex-1 px-6 py-3 bg-gray-300 text-gray-900 rounded-lg hover:bg-gray-400 transition font-medium text-center">
                            Back to Dashboard
                        </a>
                    </div>
                </form>

                <!-- Example Data Section -->
                <div class="mt-12 pt-8 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Test Data</h3>
                    <p class="text-gray-600 text-sm mb-4">Click to populate the RFID field:</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        @php
                            $testRfids = ['RFID-001', 'RFID-002', 'RFID-003', 'RFID-004'];
                        @endphp
                        @foreach ($testRfids as $rfid)
                            <button type="button"
                                onclick="document.getElementById('rfid_uid').value = '{{ $rfid }}'; document.getElementById('rfid_uid').focus();"
                                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded text-sm font-medium transition">
                                {{ $rfid }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
