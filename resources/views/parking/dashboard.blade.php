@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50 py-12 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8 flex items-start justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900">Dashboard Monitoring Parkir</h1>
                    <p class="text-gray-600 mt-2">Monitoring real-time parkir berbasis RFID</p>
                </div>
                <div class="flex gap-3">
                    @auth
                        <a href="{{ url('/admin') }}"
                            class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.26 2.632 1.732-.203.341-.407.682-.612 1.023a1.704 1.704 0 002.010 2.010c.341-.205.682-.409 1.023-.612 1.471-.678 2.672 1.089 1.732 2.632a1.704 1.704 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.704 1.704 0 00-1.066 2.573c.94 1.543-.26 3.31-1.732 2.632-.341-.203-.682-.407-1.023-.612a1.704 1.704 0 00-2.010 2.010c.205.341.409.682.612 1.023.678 1.471-1.089 2.672-2.632 1.732a1.704 1.704 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.704 1.704 0 00-2.573-1.066c-1.543.94-3.31-.26-2.632-1.732.203-.341.407-.682.612-1.023a1.704 1.704 0 00-2.010-2.010c-.341.205-.682.409-1.023.612-1.471.678-2.672-1.089-1.732-2.632a1.704 1.704 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.704 1.704 0 001.066-2.573c-.94-1.543.26-3.31 1.732-2.632.341.203.682.407 1.023.612a1.704 1.704 0 002.010-2.010c-.205-.341-.409-.682-.612-1.023-.678-1.471 1.089-2.672 2.632-1.732a1.704 1.704 0 002.573-1.066zM12 15a3 3 0 100-6 3 3 0 000 6z">
                                </path>
                            </svg>
                            Admin Panel
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                            Login
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Alerts -->
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="text-red-800">
                        <p class="font-semibold">Error!</p>
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
                        <p class="font-semibold">Berhasil!</p>
                        <p class="mt-1">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="text-yellow-800">
                        <p class="font-semibold">Peringatan</p>
                        <p class="mt-1">{{ session('warning') }}</p>
                    </div>
                </div>
            @endif

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Sesi Aktif</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">
                                {{ $sessions->where('check_out_at', null)->count() }}
                            </p>
                        </div>
                        <svg class="w-12 h-12 text-blue-500 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z">
                            </path>
                            <path
                                d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z">
                            </path>
                        </svg>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Sesi Overtime</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">
                                {{ $sessions->where('check_out_at', null)->filter(fn($s) => $s->status === 'OVERTIME')->count() }}
                            </p>
                        </div>
                        <svg class="w-12 h-12 text-red-500 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-gray-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Sesi</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $sessions->count() }}</p>
                        </div>
                        <svg class="w-12 h-12 text-gray-500 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mb-8 flex gap-4">
                <a href="{{ route('parking.history') }}"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Lihat Riwayat
                </a>
            </div>

            <!-- Sessions Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Sesi Parkir Aktif</h2>
                    <p class="text-sm text-gray-600 mt-1">Auto-refresh setiap 30 detik</p>
                </div>

                @if ($sessions->where('check_out_at', null)->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100 border-b border-gray-200">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        UID RFID</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Nomor Kendaraan</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Check In</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Waktu Habis</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Sisa Waktu</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Status</th>
                                    @auth
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Aksi</th>
                                    @endauth
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($sessions->where('check_out_at', null) as $session)
                                    @php
                                        $isOvertime = $session->remaining_minutes < 0;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition @if ($isOvertime) bg-red-50 @endif"
                                        id="session-{{ $session->id }}"
                                        data-overtime="{{ $isOvertime ? 'true' : 'false' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <code
                                                class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $session->rfid_uid }}</code>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $session->rfid?->vehicle_number ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $session->check_in_at->format('H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $session->expires_at->format('H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                            @php
                                                $remaining = $session->remaining_minutes;
                                            @endphp
                                            @if ($remaining >= 0)
                                                <div class="flex items-center gap-1">
                                                    <span class="inline-block w-2 h-2 bg-green-500 rounded-full"></span>
                                                    <span
                                                        class="text-green-600">{{ $session->formatted_remaining_time }}</span>
                                                </div>
                                            @else
                                                <div class="flex flex-col">
                                                    <span class="text-red-600 font-bold flex items-center gap-1">
                                                        <span class="inline-block w-2 h-2 bg-red-500 rounded-full"></span>
                                                        Terlewat
                                                    </span>
                                                    <span class="text-red-500 text-xs">{{ abs($remaining) }} menit</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $status = $session->status;
                                                $badgeClass = match ($status) {
                                                    'NORMAL' => 'bg-green-100 text-green-800',
                                                    'OVERTIME' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800',
                                                };
                                            @endphp
                                            <span
                                                class="px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                                {{ $status }}
                                            </span>
                                        </td>
                                        @auth
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <form action="{{ route('parking.check-out', $session) }}" method="POST"
                                                    class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="text-blue-600 hover:text-blue-900 font-medium text-sm">
                                                        Check Out
                                                    </button>
                                                </form>
                                            </td>
                                        @endauth
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Tidak Ada Sesi Aktif</h3>
                        <p class="mt-2 text-gray-600">Semua sesi parkir sudah checkout.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Overtime Alert Modal -->
    <div id="overtimeModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4 hidden"
        style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden flex flex-col">
            <!-- Modal Header dengan Background Animation -->
            <div
                class="relative bg-gradient-to-br from-red-600 via-red-500 to-orange-500 text-white px-8 py-8 flex items-center justify-between overflow-hidden">
                <!-- Animated Background -->
                <div class="absolute inset-0 opacity-10">
                    <div
                        class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full mix-blend-multiply filter blur-3xl animate-pulse">
                    </div>
                </div>

                <div class="relative z-10 flex items-center gap-3">
                    <div class="animate-bounce">
                        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-black">PARKIR OVERTIME!</h2>
                </div>

                <button onclick="closeOvertimeModal()"
                    class="relative z-10 hover:bg-red-700 hover:bg-opacity-50 p-2 rounded-lg transition transform hover:scale-110">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="overflow-y-auto flex-1 p-8 max-h-72 bg-gradient-to-b from-gray-50 to-white">
                <p class="text-sm text-gray-600 font-semibold mb-4 uppercase tracking-wide">Daftar Kendaraan yang
                    Terdeteksi Overtime:</p>
                <div id="overtimeList" class="space-y-3">
                    <!-- Akan diisi oleh JavaScript -->
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-100 px-8 py-4 flex justify-end gap-3 border-t border-gray-200">
                <button onclick="closeOvertimeModal()"
                    class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition font-bold text-sm shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    ✕ Tutup Alert
                </button>
            </div>
        </div>
    </div>

    <!-- Pulse Animation CSS -->
    <style>
        @keyframes pulse {

            0%,
            100% {
                background-color: rgb(254, 242, 242);
            }

            50% {
                background-color: rgb(254, 202, 202);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        @keyframes slideInScale {
            0% {
                opacity: 0;
                transform: scale(0.95) translateY(20px);
            }

            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .modal-shake {
            animation: shake 0.5s cubic-bezier(0.36, 0, 0.66, 1);
        }

        .animate-in {
            animation: slideInScale 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .animate-bounce {
            animation: float 2s ease-in-out infinite;
        }
    </style>

    <!-- Auto Refresh Script (30 seconds) -->
    <script>
        (function() {
            // Set auto-refresh to 60 seconds
            const refreshInterval = 60000; // 60 seconds in milliseconds
            let lastAlarmTime = 0; // Track last alarm time
            const alarmCooldown = 5000; // Cooldown 5 detik antara alarm
            let audioContext = null;
            let oscillator = null;
            let gainNode = null;
            let isAlarmPlaying = false;

            // Initialize audio context
            function initAudioContext() {
                if (!audioContext) {
                    try {
                        audioContext = new(window.AudioContext || window.webkitAudioContext)();
                    } catch (e) {
                        console.error('Audio context not supported:', e);
                    }
                }
                return audioContext;
            }

            // Check for overtime sessions and show modal
            function checkOvertimeSessions() {
                const overtimeSessions = document.querySelectorAll('[data-overtime="true"]');
                const currentTime = Date.now();

                console.log('Checking overtime sessions:', overtimeSessions.length);

                if (overtimeSessions.length > 0) {
                    // Hanya trigger alarm jika belum dalam cooldown period
                    if (currentTime - lastAlarmTime > alarmCooldown) {
                        console.log('Triggering alarm and modal...');
                        showOvertimeModal(overtimeSessions);
                        playAlarmContinuous();
                        lastAlarmTime = currentTime;
                    }
                }
            }

            // Show overtime modal
            function showOvertimeModal(overtimeSessions) {
                const modal = document.getElementById('overtimeModal');
                const list = document.getElementById('overtimeList');

                // Clear previous list
                list.innerHTML = '';

                // Build list of overtime vehicles
                overtimeSessions.forEach(row => {
                    const uid = row.querySelector('td:nth-child(1)')?.textContent?.trim() || 'Unknown';
                    const vehicle = row.querySelector('td:nth-child(2)')?.textContent?.trim() || 'Unknown';
                    const overtime = row.querySelector('td:nth-child(5) .text-xs')?.textContent?.trim() ||
                        'Unknown';

                    const item = document.createElement('div');
                    item.className =
                        'flex items-center justify-between p-4 bg-white border-2 border-red-100 rounded-xl hover:border-red-400 hover:shadow-lg transition group';
                    item.innerHTML = `
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 text-base group-hover:text-red-600 transition truncate">🚗 ${vehicle}</p>
                            <p class="text-xs text-gray-500 mt-1">🏷 UID: <code class="bg-gray-100 px-2 py-1 rounded text-gray-700">${uid}</code></p>
                        </div>
                        <div class="text-right ml-4 flex-shrink-0 px-4 py-3 bg-gradient-to-br from-red-100 to-orange-100 rounded-xl">
                            <p class="text-red-700 font-black text-lg">${overtime}</p>
                        </div>
                    `;
                    list.appendChild(item);
                });

                // Show modal
                modal.style.display = 'flex';
                modal.classList.remove('hidden');

                // Shake animation
                modal.querySelector('.bg-white').classList.add('modal-shake');
            }

            // Play alarm continuously (loop)
            function playAlarmContinuous() {
                if (isAlarmPlaying) {
                    console.log('Alarm already playing');
                    return;
                }

                try {
                    const ctx = initAudioContext();
                    if (!ctx) {
                        console.error('Audio context not available');
                        return;
                    }

                    // Resume if suspended
                    if (ctx.state === 'suspended') {
                        ctx.resume().then(() => {
                            console.log('Audio context resumed');
                            startAlarmBeep(ctx);
                        });
                    } else {
                        startAlarmBeep(ctx);
                    }
                } catch (e) {
                    console.error('Error starting alarm:', e);
                }
            }

            // Start beeping alarm
            function startAlarmBeep(ctx) {
                isAlarmPlaying = true;
                console.log('Starting alarm beep...');

                try {
                    oscillator = ctx.createOscillator();
                    gainNode = ctx.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(ctx.destination);

                    oscillator.frequency.value = 800; // 800 Hz
                    oscillator.type = 'sine';

                    // Start with initial beep pattern
                    const now = ctx.currentTime;

                    // Beep 1
                    gainNode.gain.setValueAtTime(0.3, now);
                    gainNode.gain.setValueAtTime(0, now + 0.2);

                    // Beep 2
                    gainNode.gain.setValueAtTime(0.3, now + 0.3);
                    gainNode.gain.setValueAtTime(0, now + 0.5);

                    // Beep 3
                    gainNode.gain.setValueAtTime(0.3, now + 0.6);
                    gainNode.gain.setValueAtTime(0, now + 0.8);

                    // Silence for 0.5s then restart
                    gainNode.gain.setValueAtTime(0, now + 0.8);
                    gainNode.gain.setValueAtTime(0.3, now + 1.3);
                    gainNode.gain.setValueAtTime(0, now + 1.5);

                    oscillator.start(now);

                    // Loop alarm every 2 seconds while modal is open
                    const loopInterval = setInterval(() => {
                        const modal = document.getElementById('overtimeModal');
                        if (modal.style.display === 'none' || modal.classList.contains('hidden')) {
                            // Modal closed, stop alarm
                            stopAlarm();
                            clearInterval(loopInterval);
                        } else {
                            // Continue alarm
                            try {
                                oscillator.stop();
                                oscillator = audioContext.createOscillator();
                                gainNode = audioContext.createGain();

                                oscillator.connect(gainNode);
                                gainNode.connect(audioContext.destination);

                                oscillator.frequency.value = 800;
                                oscillator.type = 'sine';

                                const now = audioContext.currentTime;

                                // Beep 1
                                gainNode.gain.setValueAtTime(0.3, now);
                                gainNode.gain.setValueAtTime(0, now + 0.2);

                                // Beep 2
                                gainNode.gain.setValueAtTime(0.3, now + 0.3);
                                gainNode.gain.setValueAtTime(0, now + 0.5);

                                // Beep 3
                                gainNode.gain.setValueAtTime(0.3, now + 0.6);
                                gainNode.gain.setValueAtTime(0, now + 0.8);

                                oscillator.start(now);
                                oscillator.stop(now + 1.5);
                            } catch (e) {
                                console.error('Loop error:', e);
                                clearInterval(loopInterval);
                            }
                        }
                    }, 2000);

                    oscillator.stop(now + 1.5);
                } catch (e) {
                    console.error('Beep error:', e);
                }
            }

            // Stop alarm
            function stopAlarm() {
                console.log('Stopping alarm...');
                isAlarmPlaying = false;

                try {
                    if (oscillator) {
                        oscillator.stop();
                    }
                } catch (e) {
                    console.log('Oscillator already stopped');
                }
            }

            // Global function to close modal
            window.closeOvertimeModal = function() {
                const modal = document.getElementById('overtimeModal');
                modal.style.display = 'none';
                modal.classList.add('hidden');
                stopAlarm();
                console.log('Modal closed, alarm stopped');
            };

            // Request notification permission
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    console.log('Notification permission:', permission);
                });
            }

            // Check for overtime on page load
            console.log('Dashboard loaded, checking for overtime...');
            checkOvertimeSessions();

            // Auto-reload page
            console.log('Setting auto-refresh interval:', refreshInterval, 'ms');
            setTimeout(function() {
                console.log('Auto-refreshing page...');
                location.reload();
            }, refreshInterval);

            // Optional: Log page status untuk debugging
            setInterval(function() {
                const overtimeSessions = document.querySelectorAll('[data-overtime="true"]');
                console.log('Current overtime sessions:', overtimeSessions.length);
            }, 10000); // Log setiap 10 detik
        })();
    </script>
@endsection
