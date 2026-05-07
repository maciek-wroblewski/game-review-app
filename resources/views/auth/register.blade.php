@extends('layouts.app')

@section('title', 'GameHub - Register')

@section('content')
<div class="flex items-center justify-center py-12 md:py-16">
    <div class="w-full max-w-md bg-gray-800/80 backdrop-blur-md rounded-xl shadow-2xl border border-gray-700 p-8">
        <h2 class="text-3xl font-black text-white text-center mb-8 tracking-tight">JOIN THE CLUB</h2>
        
        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-400 mb-2">Username</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus 
                       class="block w-full rounded-lg bg-gray-900/50 border border-gray-700 text-white shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 px-4 py-3 transition">
                @error('name') 
                    <span class="text-red-400 text-xs mt-2 block font-medium">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-400 mb-2">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required 
                       class="block w-full rounded-lg bg-gray-900/50 border border-gray-700 text-white shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 px-4 py-3 transition">
                @error('email') 
                    <span class="text-red-400 text-xs mt-2 block font-medium">{{ $message }}</span> 
                @enderror
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-400 mb-2">Password</label>
                <input id="password" type="password" name="password" required 
                       class="block w-full rounded-lg bg-gray-900/50 border border-gray-700 text-white shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 px-4 py-3 transition">
                @error('password') 
                    <span class="text-red-400 text-xs mt-2 block font-medium">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-400 mb-2">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required 
                       class="block w-full rounded-lg bg-gray-900/50 border border-gray-700 text-white shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 px-4 py-3 transition">
            </div>
            
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3.5 rounded-lg transition-all transform hover:scale-[1.02] shadow-lg shadow-indigo-500/20 uppercase tracking-wider text-sm">
                Create Account
            </button>
        </form>
        
        <p class="mt-8 text-center text-gray-400 text-sm">
            Already have an account? <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-bold transition">Log in</a>
        </p>
    </div>
</div>
@endsection
