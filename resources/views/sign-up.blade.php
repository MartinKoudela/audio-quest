<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AudioQuest - Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-cover bg-center"
      style="background-image: url('background.png');">

<div class="relative backdrop-blur-lg bg-white/10 border border-white/20 rounded-2xl p-8 w-[350px] shadow-xl">

    <h1 class="text-white text-center text-xl mb-6 tracking-wide">
        Create Account
    </h1>

    <form class="space-y-4">

        <input
            type="text"
            placeholder="Username"
            class="w-full px-4 py-3 rounded-full bg-white/20 text-white placeholder-gray-300 outline-none focus:ring-2 focus:ring-teal-400"
        >

        <input
            type="email"
            placeholder="Email"
            class="w-full px-4 py-3 rounded-full bg-white/20 text-white placeholder-gray-300 outline-none focus:ring-2 focus:ring-teal-400"
        >

        <input
            type="password"
            placeholder="Password"
            class="w-full px-4 py-3 rounded-full bg-white/20 text-white placeholder-gray-300 outline-none focus:ring-2 focus:ring-teal-400"
        >

        <div class="flex items-center text-sm text-gray-300 gap-2 pl-1">
            <input type="checkbox" class="mr-3 ml-1 accent-[#063C50]">
            <label>
                I agree to the Terms & Conditions*
            </label>
        </div>

        <button
            type="submit"
            class="w-full py-3 rounded-full bg-[#063C50] hover:bg-[#052f3f] text-white font-semibold transition"
        >
            SIGN UP
        </button>

    </form>

    <div class="text-center text-sm text-gray-300 mt-4">
        Already have an account?
        <a href="#" class="hover:text-white">
            Login
        </a>
    </div>

</div>

</body>
</html>
