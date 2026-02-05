@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold">Admin Dashboard</h1>
        <p class="text-gray-600 mt-2">Sistem istatistikleri ve özeti</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Events -->
        <div class="bg-white border rounded-lg p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Toplam Etkinlik</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $stats['total_events'] }}</p>
                </div>
                <div class="text-4xl text-blue-200">📅</div>
            </div>
        </div>

        <!-- Total Organizers -->
        <div class="bg-white border rounded-lg p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Toplam Organizer</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $stats['total_organizers'] }}</p>
                </div>
                <div class="text-4xl text-indigo-200">🎭</div>
            </div>
        </div>

        <!-- Total Attendees -->
        <div class="bg-white border rounded-lg p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Toplam Attendee</p>
                    <p class="text-3xl font-bold text-teal-600 mt-2">{{ $stats['total_attendees'] }}</p>
                </div>
                <div class="text-4xl text-teal-200">👥</div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white border rounded-lg p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Toplam Sipariş</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $stats['total_orders'] }}</p>
                </div>
                <div class="text-4xl text-green-200">📦</div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white border rounded-lg p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Toplam Gelir (PAID)</p>
                    <p class="text-3xl font-bold text-emerald-600 mt-2">{{ number_format($stats['total_revenue'], 2) }} ₺</p>
                </div>
                <div class="text-4xl text-emerald-200">💰</div>
            </div>
        </div>

        <!-- Total Tickets -->
        <div class="bg-white border rounded-lg p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Toplam Bilet</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">{{ $stats['total_tickets'] }}</p>
                </div>
                <div class="text-4xl text-purple-200">🎫</div>
            </div>
        </div>

        <!-- Checked In Tickets -->
        <div class="bg-white border rounded-lg p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Kullanılan Bilet</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $stats['checked_in_tickets'] }}</p>
                </div>
                <div class="text-4xl text-yellow-200">✅</div>
            </div>
        </div>

        <!-- Paid Orders -->
        <div class="bg-white border rounded-lg p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Ödenen Sipariş</p>
                    <p class="text-3xl font-bold text-cyan-600 mt-2">{{ $stats['paid_orders'] }}</p>
                </div>
                <div class="text-4xl text-cyan-200">💳</div>
            </div>
        </div>
    </div>

    <!-- Today's Statistics -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-bold mb-4 text-blue-900">📊 Bugünkü İstatistikler</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <p class="text-gray-600 text-sm font-medium">Bugün Yapılan Satış</p>
                <p class="text-2xl font-bold text-blue-600 mt-2">{{ $stats['today_orders'] }} sipariş</p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <p class="text-gray-600 text-sm font-medium">Bugün Elde Edilen Gelir</p>
                <p class="text-2xl font-bold text-green-600 mt-2">{{ number_format($stats['today_revenue'], 2) }} ₺</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white border rounded-lg p-6 shadow-sm">
        <h2 class="text-xl font-bold mb-4">Hızlı Erişim</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <a href="{{ route('admin.events.index') }}" class="block bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg p-4 transition">
                <p class="font-semibold text-blue-600">📅 Etkinlikler</p>
                <p class="text-sm text-gray-600 mt-1">Tüm etkinlikleri yönetin</p>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="block bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg p-4 transition">
                <p class="font-semibold text-green-600">📦 Siparişler</p>
                <p class="text-sm text-gray-600 mt-1">Tüm siparişleri görmek</p>
            </a>
            <a href="{{ route('admin.tickets.index') }}" class="block bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg p-4 transition">
                <p class="font-semibold text-purple-600">🎫 Biletler</p>
                <p class="text-sm text-gray-600 mt-1">Biletleri yönetin</p>
            </a>
            <a href="{{ route('admin.users.index') }}" class="block bg-orange-50 hover:bg-orange-100 border border-orange-200 rounded-lg p-4 transition">
                <p class="font-semibold text-orange-600">👥 Kullanıcılar</p>
                <p class="text-sm text-gray-600 mt-1">Rol ve kullanıcı yönetimi</p>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="block bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg p-4 transition">
                <p class="font-semibold text-slate-700">📊 Raporlar</p>
                <p class="text-sm text-gray-600 mt-1">Etkinlik bazlı satış raporu</p>
            </a>
        </div>
    </div>
</div>
@endsection
