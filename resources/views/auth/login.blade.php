@extends('layouts.app')

@section('title', 'GameHub - Login')

@section('content')
<div class="flex items-center justify-center py-12 md:py-24">
    <div class="w-full max-w-md bg-gray-800/80 backdrop-blur-md rounded-xl shadow-2xl border border-gray-700 p-8">
        <h2 class="text-3xl font-black text-white text-center mb-8 tracking-tight">WELCOME BACK</h2>
        
        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-400 mb-2">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus 
                       class="block w-full rounded-lg bg-gray-900/50 border border-gray-700 text-white shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 px-4 py-3 transition">
                @error('email') 
                    <span class="text-red-400 text-xs mt-2 block font-medium">{{ $message }}</span> 
                @enderror
            </div>
            
            <div>
                <div class="flex justify-between items-baseline mb-2">
                    <label for="password" class="block text-sm font-medium text-gray-400">Password</label>
                    <a href="#" class="text-xs text-indigo-400 hover:text-indigo-300">Forgot password?</a>
                </div>
                <input id="password" type="password" name="password" required 
                       class="block w-full rounded-lg bg-gray-900/50 border border-gray-700 text-white shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 px-4 py-3 transition">
                @error('password') 
                    <span class="text-red-400 text-xs mt-2 block font-medium">{{ $message }}</span> 
                @enderror
            </div>
            
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3.5 rounded-lg transition-all transform hover:scale-[1.02] shadow-lg shadow-indigo-500/20 uppercase tracking-wider text-sm">
                Log In
            </button>
        </form>
        
        <p class="mt-8 text-center text-gray-400 text-sm">
            Don't have an account? <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300 font-bold transition">Sign up</a>
        </p>
    </div>
</div>
@endsection
